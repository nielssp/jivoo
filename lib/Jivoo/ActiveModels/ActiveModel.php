<?php
/**
 * An active model containing active records, see also {@see ActiveRecord}.
 * @package Jivoo\ActiveModels
 */
abstract class ActiveModel extends Model implements IEventListener {
  /**
   * @var string Name of database used by model.
   */
  protected $database = 'default';

  /**
   * @var string Name of database table used by model, null for default based
   * on name of model.
   */
  protected $table = null;

  /**
   * @var string Name of custom {@see ActiveRecord} implementation to use.
   */
  protected $record = null;

  /**
   * @var array Array containing one-to-many association definitions.
   */
  protected $hasMany = array();

  /**
   * @var array Array containing many-to-many association definitions.
   */
  protected $hasAndBelongsToMany = array();
  
  /**
   * @var array Array containing one-to-one association definitions.
   */
  protected $belongsTo = array();

  /**
   * @var array Array containing one-to-one association definititions.
   */
  protected $hasOne = array();

  /**
   * @var array Array containing validation rules, see {@see Validator}.
   */
  protected $validate = array();

  /**
   * @var string[] Maps field names to GUI labels, will be translated.
   */
  protected $labels = array();

  /**
   * @var string[] List of mxin classes to load, must extend
   * {@see ActiveModelMixin}.
   */
  protected $mixins = array();
  
  /**
   * @var string[] List of virtual field names.
   */
  protected $virtual = array();

  /**
   * @var string[] Custom getters, maps field name to method name.
   */
  protected $getters = array();

  /**
   * @var string[] Custom setters, maps field name to method name.
   */
  protected $setters = array();

  /**
   * @var stiring[] Associative array mapping between actions and routes.
   */
  protected $actions = array();
  
  /**
   * {@inheritdoc}
   */
  protected $events = array('beforeSave', 'afterSave', 'beforeValidate', 'afterValidate', 'afterCreate', 'afterLoad', 'beforeDelete', 'install');

  /**
   * @var Table Model source.
   */
  private $source;
  
  /**
   * @var string Name of model.
   */
  private $name;
  
  /**
   * @var ActiveModelMixin[] Mixin objects.
   */
  private $mixinObjects = array();
  
  /**
   * @var Schema Model schema.
   */
  private $schema;
  
  /**
   * @var string[] Names of all model fields.
   */
  private $fields = array();

  /**
   * @var string[] Name of non-virtual model fields.
   */
  private $nonVirtualFields = array();
  
  /**
   * @var string[] Name of virtual model fields.
   */
  private $virtualFields = array();
  
  /**
   * @var Validator Model validator.
   */
  private $validator;

  /**
   * @var array Array of all associations.
   */
  private $associations = null;

  /**
   * @var string Name of primary key.
   */
  private $primaryKey = null;
  
  /**
   * @var string Name of primary key if auto incrementing.
   */
  private $aiPrimaryKey = null;

  /**
   * @var array Array of default values.
   */
  private $defaults = array();

  /**
   * @var ActiveRecord[] Cache of already loaded records.
   */
  private $cache = array();
  
  /**
   * Construct active model.
   * @param App $app Associated application.
   * @param Databases $databases Databases module.
   * @throws DataSourceNotFoundException If database not configured.
   * @throws TableNotFoundException If table not found.
   * @throws InvalidPrimaryKeyException If primary key is invalid.
   * @throws InvalidRecordClassException If record class is invalid.
   * @throws ClassNotFoundException If association models are not found.
   * @throws InvalidMixinException If a mixin is invalid.
   */
  public final function __construct(App $app, Databases $databases) {
    parent::__construct($app);
    $databaseName = $this->database;
    $database = $databases->$databaseName;
    if (!isset($database))
      throw new DataSourceNotFoundException(tr(
        'Database "%1" not found in model %2', $this->database, $this->name
      ));
    $this->database = $database;
    $this->name = get_class($this);
    if (!isset($this->table))
      $this->table = $this->name;
    $table = $this->table;
    if (!isset($this->database->$table))
      throw new TableNotFoundException(tr(
        'Table "%1" not found in model %2', $table, $this->name
      ));
    $this->source = $this->database->$table;

    $this->schema = $this->source->getSchema();
    $pk = $this->schema->getPrimaryKey();
    if (count($pk) == 1) {
      $pk = $pk[0];
      $this->primaryKey = $pk;
      $type = $this->schema->$pk;
      if ($type->isInteger() and $type->autoIncrement)
        $this->aiPrimaryKey = $pk;
    }
    else {
      throw new InvalidPrimaryKeyException(tr(
        'ActiveModel does not support multi-field primary keys'
      ));
    }
    
    $this->nonVirtualFields = $this->schema->getFields();
    $this->fields = $this->nonVirtualFields;
    foreach ($this->virtual as $field) {
      $this->fields[] = $field;
      $this->virtualFields[] = $field;
    }

    $this->validator = new Validator($this, $this->validate);
    $this->schema->createValidationRules($this->validator);

    foreach ($this->nonVirtualFields as $field) {
      $type = $this->schema->$field;
      if (isset($type->default))
        $this->defaults[$field] = $type->default;
    }

    if (isset($this->record)) {
      if (!class_exists($this->record) or !is_subclass_of($this->record, 'ActiveRecord'))
        throw new InvalidRecordClassException(tr(
          'Record class %1 must exist and extend %2', $this->record, 'ActiveRecord'
        ));
    }
    
    $this->attachEventListener($this);
    foreach ($this->mixins as $mixin => $options) {
      if (!is_string($mixin)) {
        $mixin = $options;
        $options = array();
      }
      $mixin .= 'Mixin';
      if (!Lib::classExists($mixin))
        throw new ClassNotFoundException(tr('Mixin class not found: %1', $mixin));
      if (!is_subclass_of($mixin, 'ActiveModelMixin'))
        throw new InvalidMixinException(tr('Mixin class %1 must extend ActiveModelMixin', $mixin));
      $mixin = new $mixin($this->app, $this, $options);
      $this->attachEventListener($mixin);
      $this->mixinObjects[] = $mixin;
    }

    $this->database->$table = $this;
  }
  
  /**
   * Add a virtual field to model.
   * @param string $field Name of field.
   */
  public function addVirtual($field) {
    $this->fields[] = $field;
    $this->virtualFields[] = $field;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getEventHandlers() {
    return $this->events;
  }

  /**
   * Get default values of fields.
   * @return array Associative array mapping field names to default values.
   */
  public function getDefaults() {
    return $this->defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function create($data = array(), $allowedFields = null) {
    return ActiveRecord::createNew($this, $data, $allowedFields, $this->record);
  }

  /**
   * {@inheritdoc}
   */
  public function createExisting($data = array()) {
    if (isset($data[$this->primaryKey])) {
      $id = $data[$this->primaryKey];
      if (isset($this->cache[$id]))
        return $this->cache[$id];
    }
    $data = $this->source->createExisting($data)->getData();
    return ActiveRecord::createExisting($this, $data, $this->record);
  }

  /**
   * Add record to model cache.
   * @param ActiveRecord $record Record.
   */
  public function addToCache(ActiveRecord $record) {
    $pk = $this->primaryKey;
    $this->cache[$record->$pk] = $record;
  }

  /**
   * Get name of associated database.
   * @return string Name of database connection.
   */
  public function getDatabase() {
    return $this->database;
  }

  /**
   * {@inheritdoc}
   */
  public function getAiPrimaryKey() {
    return $this->aiPrimaryKey;
  }
  
  /**
   * Get model associations.
   * @return array Array of all associations with options.
   */
  public function getAssociations() {
    if (!isset($this->associations))
      $this->createAssociations();
    return $this->associations;
  }

  /**
   * Get route for record.
   * @param ActiveRecord $record A record.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function getRoute(ActiveRecord $record) {
    return null;
  }

  /**
   * Get route for an action defined in model.
   * @param ActiveRecord $record A record.
   * @param string $action Name of an action.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function getAction(ActiveRecord $record, $action) {
    if (isset($this->actions[$action])) {
      $route = $this->m->Routing->validateRoute($this->actions[$action]);
      foreach ($route['parameters'] as $key => $parameter) {
        if (preg_match('/^%(.+)%$/', $parameter, $matches) === 1) {
          $field = $matches[1];
          $route['parameters'][$key] = $record->$field;
        }
      }
      return $route;
    }
    return null;
  }

  /**
   * Create all associations.
   * @throws InvalidAssociationException If an association is invalid.
   */
  private function createAssociations() {
    foreach (array('hasOne', 'belongsTo', 'hasMany', 'hasAndBelongsToMany') as $type) {
      foreach ($this->$type as $name => $options) {
        if (!is_string($name)) {
          if (!is_string($options) or !($type == 'belongsTo' or $type == 'hasOne'))
            throw new InvalidAssociationException(tr(
              'Invalid "%1"-association in %2', $type, $this->name
            ));
          $name = lcfirst($options);
          $options = array(
            'model' => $options
          );
        }
        if (is_string($options)) {
          $options = array(
            'model' => $options
          );
        }
        $this->createAssociation($type, $name, $options);
      }
    }
  }

  /**
   * Create a single association.
   * @param string $type Type of association.
   * @param string $name Name of association
   * @param array $options Array of options for association.
   * @throws ModelNotFoundException If other model not found.
   * @throws InvalidModelException If other model not an active model.
   * @throws DataSourceNotFoundException If join source not found.
   */
  private function createAssociation($type, $name, $options) {
    $options['type'] = $type;
    $otherModel = $options['model'];
    if (!isset($this->database->$otherModel)) {
      throw new ModelNotFoundException(tr(
        'Model %1 not found in  %2', $otherModel, $this->name
      ));
    }
    $options['model'] = $this->database->$otherModel;
    if (!isset($options['thisKey'])) {
      $options['thisKey'] = lcfirst($this->name) . 'Id';
    }
    if (!isset($options['otherKey'])) {
      $options['otherKey'] = lcfirst($otherModel) . 'Id';
    }
    if ($type == 'hasAndBelongsToMany') {
      if (!($options['model'] instanceof ActiveModel)) { 
        throw new InvalidModelException(tr(
          '%1 invalid for joining with %2, must extend ActivModel',
          $otherModel, $this->name
        ));
      }
      $options['otherPrimary'] = $options['model']->primaryKey;
      if (!isset($options['join'])) {
        $otherTable = $options['model']->table;
        $options['join'] = $otherTable . $this->table;
        if (strcmp($this->table, $otherTable) < 0)
          $options['join'] = $this->table .  $otherTable;
      }
      if (!isset($this->database->$options['join'])) {
        throw new DataSourceNotFoundException(tr(
          'Association data source "%1" not found', $options['join']
        ));
      }
      $options['join'] = $this->database->$options['join'];
    }
    $this->associations[$name] = $options;
  }

  /**
   * Called before saving a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function beforeSave(ActiveModelEvent $event) { }

  /**
   * Called after saving a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function afterSave(ActiveModelEvent $event) { }

  /**
   * Called before validating a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function beforeValidate(ActiveModelEvent $event) { }

  /**
   * Called after validating a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function afterValidate(ActiveModelEvent $event) { }

  /**
   * Called after creating a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function afterCreate(ActiveModelEvent $event) { }

  /**
   * Called after loading a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function afterLoad(ActiveModelEvent $event) { }

  /**
   * Called before deleting a record.
   * @param ActiveModelEvent $event Event data.
   */
  public function beforeDelete(ActiveModelEvent $event) { }

  /**
   * Install model.
   */
  public function install() { }

  /**
   * Get custom getters implemented by model.
   * @return string[] Associative array mapping field names to method names.
   */
  public function getGetters() {
    return $this->getters;
  }

  /**
   * Get custom setters implemented by model.
   * @return string[] Associative array mapping field names to method names.
   */
  public function getSetters() {
    return $this->setters;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidator() {
    return $this->validator;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired($field) {
    return $this->validator->isRequired($field);
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * Get list of virtual fields.
   * @return string[] List of virtual field names.
   */
  public function getVirtualFields() {
    return $this->virtualFields;
  }

  /**
   * Get list of non-virtual fields.
   * @return string[] List of non-virtual field names.
   */
  public function getNonVirtualFields() {
    return $this->nonVirtualFields;
  }

  /**
   * {@inheritdoc}
   */
  public function find($id) {
    if (isset($this->cache[$id]))
      return $this->cache[$id];
    return $this->where($this->primaryKey . ' = ?', $id)->first();
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel($field) {
    if (!isset($this->labels[$field]))
      $this->labels[$field] = ucfirst(strtolower(
        preg_replace('/([A-Z])/', ' $1', lcfirst($field))
      ));
    return tr($this->labels[$field]);
  }

  /**
   * {@inheritdoc}
   */
  public function updateSelection(UpdateSelection $selection) {
    return $this->source->updateSelection($selection);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSelection(DeleteSelection $selection) {
    return $this->source->deleteSelection($selection);
  }

  /**
   * {@inheritdoc}
   */
  public function countSelection(ReadSelection $selection) {
    return $this->source->countSelection($selection);
  }

  /**
   * {@inheritdoc}
   */
  public function firstSelection(ReadSelection $selection) {
    $resultSet = $this->source->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }

  /**
   * {@inheritdoc}
   */
  public function lastSelection(ReadSelection $selection) {
    $resultSet = $this->source->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }

  /**
   * {@inheritdoc}
   */
  public function read(ReadSelection $selection) {
    $resultSet = $this->source->readSelection($selection);
    return new ResultSetIterator($this, $resultSet);
  }

  /**
   * {@inheritdoc}
   */
  public function readCustom(ReadSelection $selection) {
    return $this->source->readCustom($selection);
  }

  /**
   * {@inheritdoc}
   */
  public function insert($data) {
    return $this->source->insert($data);
  }

  /**
   * Get an association.
   * @param ActiveRecord $record A record.
   * @param array $association Association options.
   * @throws InvalidAssociationException If association type unknown.
   * @return ActiveCollection|ActiveRecord|null A collection, a record or null
   * depending on association type.
   */
  public function getAssociation(ActiveRecord $record, $association) {
    switch ($association['type']) {
      case 'belongsTo':
        $key = $association['otherKey'];
        if (!isset($record->$key))
          return null;
        // TODO fetch lazy record instead
        return $association['model']->find($record->$key);
      case 'hasOne':
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        return $association['model']->where($key . ' = ?', $record->$id)->first();
      case 'hasMany':
      case 'hasAndBelongsToMany':
        $id = $this->primaryKey;
        return new ActiveCollection($this, $record->$id, $association);
    }
    throw new InvalidAssociationException(tr('Unknown association type: %1', $association['type']));
  }

  /**
   * Whether or not an association is set an non-empty.
   * @param ActiveRecord $record A record.
   * @param array $association Association options.
   * @throws InvalidAssociationException If association type unknown.
   * @return boolean True if non-empty association.
   */
  public function hasAssociation(ActiveRecord $record, $association) {
    switch ($association['type']) {
      case 'belongsTo':
        $key = $association['otherKey'];
        return isset($record->$key);
      case 'hasOne':
      case 'hasMany':
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        return $association['model']->where($key . ' = ?', $record->$id)->count() != 0;
      case 'hasAndBelongsToMany':
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        return $association['join']->where($key . ' = ?', $record->$id)->count() != 0;
    }
    throw new InvalidAssociationException(tr('Unknown association type: %1', $association['type']));
  }

  /**
   * Unset/empty an association.
   * @param ActiveRecord $record A record.
   * @param array $association Association options.
   * @throws InvalidAssociationException IF association type unknown.
   */
  public function unsetAssociation(ActiveRecord $record, $association) {
    switch ($association['type']) {
      case 'belongsTo':
        $key = $association['otherKey'];
        $record->$key = null;
        return;
      case 'hasOne':
      case 'hasMany':
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        $association['model']->where($key . ' = ?', $record->$id)->set($key, null)->update();
        return;
      case 'hasAndBelongsToMany':
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        $association['join']->where($key . ' = ?', $record->$id)->delete();
        return;
    }
    throw new InvalidAssociationException(tr('Unknown association type: %1', $association['type']));
  }

  /**
   * Set association.
   * @param ActiveRecord $record A record.
   * @param array $association Association options.
   * @param ActiveRecord|ISelection|ActiveRecord[] $value New value.
   * @throws InvalidAssociationException If association type unknown.
   */
  public function setAssociation(ActiveRecord $record, $association, $value) {
    switch ($association['type']) {
      case 'belongsTo':
        assume($value instanceof ActiveRecord);
        assume($value->getModel() == $association['model']);
        $key = $association['otherKey'];
        $otherId = $association['model']->primaryKey;
        $record->$key = $value->$otherId;
        return;
      case 'hasOne':
        assume($value instanceof ActiveRecord);
        assume($value->getModel() == $association['model']);
        $this->unsetAssociation($record, $association);
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        $value->$key = $record->$id;
        $value->save();
        return;
      case 'hasMany':
        $key = $association['thisKey'];
        $id = $this->primaryKey;
        $idValue = $record->$id;
        if ($value instanceof ISelection) {
          $value->set($key, $idValue)->update();
          return;
        }
        if (!is_array($value))
          $value = array($value);
        $this->unsetAssociation($record, $association);
        foreach ($value as $item) {
          assume($item instanceof ActiveRecord);
          assume($item->getModel() == $association['model']);
          $item->$key = $idValue;
          if (!$item->isNew())
            $item->save();
        }
        return;
      case 'hasAndBelongsToMany':
        return;
    }
    throw new InvalidAssociationException(tr('Unknown association type: %1', $association['type']));
  }
}

/**
 * A record class is invalid.
 * @package Jivoo\ActiveModels
 */
class InvalidRecordClassException extends Exception { } 

/**
 * A data source was not found
 * @package Jivoo\ActiveModels
 */
class DataSourceNotFoundException extends Exception { }

/**
 * An association is invalid.
 * @package Jivoo\ActiveModels
 */
class InvalidAssociationException extends Exception { }

/**
 * A mixin is invalid.
 * @package Jivoo\ActiveModels
 */
class InvalidMixinException extends Exception { }

/**
 * A data model is invalid.
 * @package Jivoo\ActiveModels
 */
class InvalidModelException extends Exception { }

/**
 * Event data for an active model event.
 * @package Jivoo\ActiveModels
 */
class ActiveModelEvent extends Event {
  /**
   * @var ActiveRecord Subject of event.
   */
  public $record = null;
  
  /**
   * Construct active model event.
   * @param mixed $sender Sender
   */
  public function __construct($sender) {
    parent::__construct($sender);
    if ($sender instanceof ActiveRecord)
      $this->record = $sender;
  }
}
