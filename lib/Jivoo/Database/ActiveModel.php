<?php

abstract class ActiveModel extends Model implements IActiveModelEvents {

  protected $table = null;

  protected $record = null;

  protected $hasMany = array();

  protected $hasAndBelongsToMany = array();

  protected $belongsTo = array();

  protected $hasOne = array();

  protected $validate = array();

  protected $labels = array();

  protected $mixins = array();
  
  protected $virtual = array();

  protected $getters = array();

  protected $setters = array();

  /**
   * @var Table
   */
  private $source;
  /**
   * @var IDatabase
   */
  private $database;
  
  private $name;
  
  private $mixinObjects = array();
  
  /**
   * @var Schema
   */
  private $schema;
  
  private $fields = array();
  private $nonVirtualFields = array();
  private $virtualFields = array();
  
  private $validator;

  private $associations = null;

  private $primaryKey = null;
  private $aiPrmaryKey = null;

  private $defaults = array();

  private $cache = array();
  
  public final function __construct(IDatabase $database) {
    $this->database = $database;
    $this->name = preg_replace('/Model$/', '', get_class($this));
    if (!isset($this->table))
      $this->table = $this->name;
    $table = $this->table;
    if (!$this->database->tableExists($table))
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
      $this->mixinObjects[] = new $mixin($this, $options);
    }
  }

  public function getDefaults() {
    return $this->defaults;
  }

  public function create($data = array(), $allowedFields = null) {
    return ActiveRecord::createNew($this, $data, $allowedFields, $this->record);
  }
  
  public function createExisting($data = array()) {
    if (isset($data[$this->primaryKey])) {
      $id = $data[$this->primaryKey];
      if (isset($this->cache[$id]))
        return $this->cache[$id];
    }
    return ActiveRecord::createExisting($this, $data, $this->record);
  }

  public function addToCache(ActiveRecord $record) {
    $pk = $this->primaryKey;
    $this->cache[$record->$pk] = $record;
  }

  public function getDatabase() {
    return $this->database;
  }

  public function getAiPrimaryKey() {
    return $this->aiPrimaryKey;
  }
  
  public function getAssociations() {
    if (!isset($this->associations))
      $this->createAssociations();
    return $this->associations;
  }

  public function getRoute(ActiveRecord $record) {
    // TODO implement
    return null;
  }

  public function getAction(ActiveRecord $record, $action) {
    // TODO implement
    return null;
  }

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

  private function mixinCall($method, $record = null) {
    foreach ($this->mixinObjects as $mixin)
      $mixin->$method($record);
  }
  
  public function beforeSave(ActiveRecord $record) {
    $this->mixinCall('beforeSave', $record);
  }
  public function afterSave(ActiveRecord $record) {
    $this->mixinCall('afterSave', $record);
  }
  
  public function beforeValidate(ActiveRecord $record) {
    $this->mixinCall('beforeValidate', $record);
  }
  public function afterValidate(ActiveRecord $record) {
    $this->mixinCall('afterValidate', $record);
  }
  
  public function afterCreate(ActiveRecord $record) {
    $this->mixinCall('afterCreate', $record);
  }
  
  public function afterLoad(ActiveRecord $record) {
    $this->mixinCall('afterLoad', $record);
  }
  
  public function beforeDelete(ActiveRecord $record) {
    $this->mixinCall('beforeDelete', $record);
  }

  public function install() {
    $this->mixinCall('install');
  }

  public function getGetters() {
    return $this->getters;
  }

  public function getSetters() {
    return $this->setters;
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getSchema() {
    return $this->schema;
  }
  
  public function getValidator() {
    return $this->validator;
  }

  public function isRequired($field) {
    return $this->validator->isRequired($field);
  }

  public function getFields() {
    return $this->fields;
  }

  public function getVirtualFields() {
    return $this->virtualFields;
  }

  public function getNonVirtualFields() {
    return $this->nonVirtualFields;
  }

  public function find($id) {
    if (isset($this->cache[$id]))
      return $this->cache[$id];
    return $this->where($this->primaryKey . ' = ?', $id)->first();
  }

  public function getLabel($field) {
    if (isset($this->labels[$field]))
      return $this->labels[$field];
    return ucfirst($field);
  }
  
  public function update(UpdateSelection $selection = null) {
    if (!isset($selection))
      $selection = new UpdateSelection($this);
    return $this->source->update($selection);
  }
  
  public function delete(DeleteSelection $selection = null) {
    if (!isset($selection))
      $selection = new DeleteSelection($this);
    return $this->source->delete($selection);
  }
  
  public function count(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    return $this->source->count($selection);
  }
  
  public function first(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $resultSet = $this->source->readSelection($selection->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }
  
  public function last(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    $resultSet = $this->source->readSelection($selection->reverseOrder()->limit(1));
    if (!$resultSet->hasRows())
      return null;
    return $this->createExisting($resultSet->fetchAssoc());
  }

  public function read(ReadSelection $selection) {
    $resultSet = $this->source->readSelection($selection);
    return new ResultSetIterator($this, $resultSet);
  }

  public function readCustom(ReadSelection $selection) {
    return $this->source->readCustom($selection);
  }
  
  public function insert($data) {
    return $this->source->insert($data);
  }

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
    throw new Exception('todo');
  }

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
    throw new Exception('todo');
  }

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
    throw new Exception('todo');
  }

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
    throw new Exception('todo');
  }
}

class InvalidRecordClassException extends Exception { } 

/**
 * A data source was not found
 * @package Jivoo\Database
 */
class DataSourceNotFoundException extends Exception { }
class InvalidAssociationException extends Exception { }
class InvalidMixinException extends Exception { }
