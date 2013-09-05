<?php
/**
 * Active model
 * @package Core\Database
 * @property-read array $associations An associative array of association
 * method names and arrays describing the association. 
 * @property-read string $table Name of associated database table
 * @property-read IDataSource $dataSource Associated data source
 * @property-read IDictionary $otherSources A collection of other data sources
 * for use with association
 * @property-read IDictionary $otherModels A collection of other models for use
 * with associations
 * @property-read string $primaryKey The primary key of the associated database
 * table
 * @property-read bool $aiPrimaryKey Whether or not primary key is an auto
 * incremented integer
 * @property-read Validator $validator Associated validator
 * @property-read string[] $columns List of column names
 */
class ActiveModel implements IModel {
  /**
   * @var string Name of the class used for records of this model
   */
  private $recordClass;

  /**
   * @var IDataSource The data source associated with this model
   */
  private $dataSource;
  
  /**
   * @var IDictionary Other models
   */
  private $otherModels;
  
  /**
   * @var IDictionary Other data sources
   */
  private $otherSources;
  
  /**
   * @var string Associated database table name
   */
  private $table;

  /**
   * @var Schema Associated database table schema
   */
  private $schema;

  /**
   * @var array An associative array of field names and labels
   */
  private $fields = array();

  /**
   * @var array An associative array of field names and default values
   */
  private $defaults = array();

  /**
   * @var string[] A list of columns in associated table
   */
  private $columns = array();

  /**
   * @var string Primary key of table
   */
  private $primaryKey;
  
  /**
   * @var bool Whether or not the primary key is an auto incremented integer
   */
  private $aiPrimaryKey = false;

  /**
   * @var array An associative array of field names and {@see Editor} objects
   */
  private $editors = array();

  /**
   * @var array An associative array of field names ad {@see Encoder} objects
   */
  private $encoders = array();
  
  /**
   * @var array An associative array of primary key values and
   * {@see ActiveRecord} objects
   */
  private $cache = array();

  /**
   * @var Validator Associated validator
   */
  private $validator;

  /**
   * @var array Settings as defined in the {@see ActiveRecord} class
   */
  private $settings;
  
  /**
   * @var array An associative array of association method names and info
   */
  private $associations;

  /**
   * Constructor. Will get settings from $recordClass
   * @param string $recordClass Name of {@see ActiveRecord} class
   * @param IDataSource $dataSource Data source for model
   * @param IDictionary $otherModels Additional models for associations 
   * @param IDictionary $otherSources Additional data sources for associations
   * @throws InvalidActiveRecordException If $recordClass does not extend
   * {@see ActiveRecord}
   */
  public final function __construct($recordClass, IDataSource $dataSource,
                                    IDictionary $otherModels = null,
                                    IDictionary $otherSources = null) {
    if (!is_subclass_of($recordClass, 'ActiveRecord')) {
      throw new InvalidActiveRecordException(
        tr('Invalid record class "%1", must extend ActiveRecord', $recordClass)
      );
    }
    $this->recordClass = $recordClass;
    $this->dataSource = $dataSource;
    if (!isset($otherModels)) {
      $this->otherModels = new Dictionary();
    }
    else {
      $this->otherModels = $otherModels;
    }
    if (!isset($otherSources)) {
      $this->otherSources = new Dictionary();
    }
    else {
      $this->otherSources = $otherSources;
    }
    $this->table = $dataSource->getName();
    $this->schema = $dataSource->getSchema();
    $this->columns = $this->schema->getColumns();
    $pk = $this->schema->getPrimaryKey();
    // TODO: Only one column primary keys supported
    $pk = $pk[0];
    $this->primaryKey = $pk;
    $pkInfo = $this->schema->$pk;
    if ($pkInfo['autoIncrement']) {
      $this->aiPrimaryKey = true;
    }

    $recordObj = new $recordClass($this, null);
    $this->settings = $recordObj->getModelSettings();

    $validateArray = $this->settings['validate'];
    $this->validator = $this->createValidator($validateArray);

    $this->fields = $this->settings['fields'];
    if (!is_array($this->fields)) {
      $fieldNames = array_unique(
        array_merge($this->columns, array_keys($this->settings['virtuals']))
      );
      $this->fields = array();
      foreach ($fieldNames as $field) {
        $this->fields[$field] = ucfirst($field);
      }
    }
    $this->defaults = $this->settings['defaults'];
    foreach ($this->columns as $column) {
      if (!isset($this->defaults[$column])) {
        if (isset($this->schema->$column)) {
          $info = $this->schema->$column;
          if (isset($info['default'])) {
            $this->defaults[$column] = $info['default'];
          }
        }
      }
    }
  }
  
  /**
   * Get name of table associated with an {@see ActiveRecord} class
   * @param string $recordClass Name of {@see ActiveRecord} class
   * @throws InvalidActiveRecordException If $recordClass does not extend
   * {@see ActiveRecord}
   * @return string Name of table
   */
  public static function getTable($recordClass) {
    if (!is_subclass_of($recordClass, 'ActiveRecord')) {
      throw new InvalidActiveRecordException(
        tr('Invalid record class "%1", must extend ActiveRecord', $recordClass)
      );
    }
    $recordObj = new $recordClass();
    $settings = $recordObj->getModelSettings();
    return $settings['table'];
  }

  /**
   * Create associations
   */
  private function createAssociations() {
    foreach (array('hasOne', 'belongsTo') as $associationType) {
      foreach ($this->settings[$associationType] as $class => $options) {
        if (is_string($options)) {
          $class = $options;
          $options = array();
        }
        $this->createAssociation($associationType, 'get', $class, $options);
        $this->createAssociation($associationType, 'set', $class, $options);
        $this->createAssociation($associationType, 'remove', $class, $options);
      }
    }
    foreach (array('hasMany', 'hasAndBelongsToMany') as $associationType) {
      foreach ($this->settings[$associationType] as $class => $options) {
        if (is_string($options)) {
          $class = $options;
          $options = array();
        }
        $this->createAssociation($associationType, 'get', $class, $options);
        $this->createAssociation($associationType, 'count', $class, $options);
        $this->createAssociation($associationType, 'has', $class, $options);
        $this->createAssociation($associationType, 'add', $class, $options);
        $this->createAssociation($associationType, 'remove', $class, $options);
      }
    }
  }
  
  /**
   * Create a single association
   * @param string $type Association type: 'hasOne', 'belongsTo', 'hasMany'
   * or 'hasAndBelongsToMany' 
   * @param string $method Method prefix: 'get', 'set', 'remove', 'get',
   * 'count', 'has', 'add' or 'remove'
   * @param string $class Name of other record class
   * @param array $options An associative array of options for the association
   * @throws ModelNotFoundException If other model not found
   * @throws InvalidModelException If model is not an ActiveRecord
   * @throws DataSourceNotFoundException If the data source required could not
   * be fou
   */
  private function createAssociation($type, $method, $class, $options) {
    if (!isset($options['class'])) {
      $options['class'] = $class;
    }
    $otherClass = $options['class'];
    if (!isset($this->otherModels->$otherClass)) {
      throw new ModelNotFoundException(tr(
        'Model for %1 not found in model for %2', $otherClass,
        $this->recordClass
      ));
    }
    $options['model'] = $this->otherModels->$otherClass;
    if (!isset($options['thisKey'])) {
      $options['thisKey'] = strtolower($this->recordClass) . '_id';
    }
    if (!isset($options['otherKey'])) {
      $options['otherKey'] = strtolower($otherClass) . '_id';
    }
    if ($type == 'hasAndBelongsToMany' AND !isset($options['join'])) {
      if ($options['model'] instanceof ActiveModel) { 
        $otherTable = $options['model']->table;
        if (strcmp($this->table, $otherTable) < 0) {
          $options['join'] = $this->table . '_' . $otherTable;
        }
        else {
          $options['join'] = $otherTable . '_' . $this->table;
        }
      }
      else {
        throw new InvalidModelException(tr(
          'Model for %1 invalid for joining with model for %2, must extend ActivRecord',
          $otherClass, $this->recordClass
        ));
      }
    }
    if (isset($options['join'])) {
      if (!isset($this->otherSources->$options['join'])) {
        throw new DataSourceNotFoundException(tr(
          'Association data source "%1" not found', $options['join']
        ));
      }
      $options['join'] = $this->otherSources->$options['join'];
    }
    $association = null;
    if ($type == 'hasMany' OR $type == 'hasAndBelongsToMany') {
      if (!isset($options['plural'])) {
        $options['plural'] = Utilities::getPlural($class);
      }
      switch ($method) {
        case 'get':
          $class = $options['plural'];
          $association = array('manyGet', $options);
          break;
        case 'count':
          $class = $options['plural'];
          $association = array('manyCount', $options);
          break;
        case 'has':
          $association = array('manyHas', $options);
          break;
        case 'add':
          $association = array('manyAdd', $options);
          break;
        case 'remove':
          $association = array('manyRemove', $options);
          break;
      }
    }
    else if ($type == 'hasOne' OR $type == 'belongsTo') {
      if (!isset($options['connection'])) {
        if ($type == 'hasOne') {
          $options['connection'] = 'other';
        }
        else {
          $options['connection'] = 'this';
        }
      }
      switch ($method) {
        case 'get':
          $association = array('oneGet', $options);
          break;
        case 'set':
          $association = array('oneSet', $options);
          break;
        case 'remove':
          $association = array('oneRemove', $options);
          break;
      }
    }
    if (isset($association)) {
      $this->associations[$method . $class] = $association;
    }
  }

  /**
   * Create validator for model
   * @param array $validateArray Validtor settings from record class
   * @return Validator The validator
   */
  private function createValidator($validateArray) {
    $validator = new Validator($validateArray);
    foreach ($this->columns as $column) {
      if ($column == $this->primaryKey) {
        continue;
      }
      if (isset($this->schema->$column)) {
        $info = $this->schema->$column;
        if ($info['type'] == 'integer') {
          if (isset($info['unsigned']) && $info['unsigned'] === true) {
            $intMin = 0;
            $intMax = 4294967295;
          }
          else {
            $intMin = -2147483648;
            $intMax = 2147483647;
          }
          $validator->$column->isInteger = true;
          if (isset($validator->$column->maxValue)) {
            $validator->$column->maxValue = min($validator->$column->maxValue, $intMax);
          }
          else {
            $validator->$column->maxValue = $intMax;
          }
          if (isset($validator->$column->minValue)) {
            $validator->$column->minValue = max($intMin, $validator->$column->minValue);
          }
          else {
            $validator->$column->minValue = $intMin;
          }
        }
        else if ($info['type'] == 'float') {
          $validator->$column->isFloat = true;
        }
        else if ($info['type'] == 'boolean') {
          $validator->$column->isBoolean = true;
        }
        if (isset($info['length']) AND $info['type'] != 'float'
          AND $info['type'] != 'integer' AND $info['type'] != 'boolean') {
          if (isset($validator->$column->maxLength)) {
            $validator->$column->maxLength = min($validator->$column->maxLength, $info['length']);
          }
          else {
            $validator->$column->maxLength = $info['length'];
          }
        }
        if (isset($info['key'])
        AND ($info['key'] == 'primary' OR $info['key'] == 'unique')) {
          $validator->$column->unique = true;
        }
        if (isset($info['null']) AND $info['null'] == false) {
          $validator->$column->null = false;
        }
      }
    }
    return $validator;
  }

  /**
   * Get value of a property
   * @param string $property Property name
   */
  public function __get($property) {
    switch ($property) {
      case 'associations':
        if (!isset($this->associations)) {
          $this->createAssociations();
        }
      case 'table':
      case 'dataSource':
      case 'otherSources':
      case 'otherModels':
      case 'primaryKey':
      case 'validator':
      case 'columns':
      case 'aiPrimaryKey':
        return $this->$property;
    }
  }

  /**
   * Call a method of format 'findBySomething', where 'Something' is the name
   * of any field in the model. Will return all records that matches the
   * one parameter, e.g. ->findByName('test') will return all records where
   * the field 'name' equals 'test'.
   * @param string $method Method name
   * @param mixed[] $parameters Parameters
   * @throws ModelMethodNotFoundException If the method does not exist 
   * @return ActiveRecord[] List of records
   */
  public function __call($method, $parameters) {
    if (substr($method, 0, 6) == 'findBy') {
      $field = str_replace('-', '_',
        Utilities::camelCaseToDashes(substr($method, 6))
      );
      if (in_array($field, $this->columns)) {
        return $this
        ->all(SelectQuery::create()->where($field . ' = ?', $parameters[0]));
      }
    }
    throw new ModelMethodNotFoundException(tr(
      'Method "%1" was not found in model "%2".', $method, $this->recordClass
    ));
  }

  /**
   * @return ActiveRecord New record
   */
  public function create($data = array(), $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    $record = new $this->recordClass($this);
    $fields = array_unique(
      array_merge(array_keys($this->defaults), array_keys($data)));
    foreach ($fields as $field) {
      try {
        if (isset($data[$field])
        AND (isset($this->fields[$field]) OR $allowedFields === true)) {
          $editor = $this->getFieldEditor($field);
          if (isset($editor)) {
            $format = $editor->getFormat();
            $record->$field = $format->toHtml($data[$field]);
          }
          else {
            $record->$field = $data[$field];
          }
        }
        else if (isset($this->defaults[$field])) {
          $value = $this->defaults[$field];
          if (is_array($value)) {
            if (is_callable($value[0])) {
              $function = array_shift($value);
              $record->$field = call_user_func_array($function, $value);
            }
          }
          else {
            $record->$field = $value;
          }
        }
      }
      catch (RecordPropertyNotFoundException $ex) {
        // ignore
      }
    }
    return $record;
  }

  public function getName() {
    return Utilities::camelCaseToDashes($this->recordClass);
  }

  public function getFields() {
    return array_keys($this->fields);
  }

  public function getFieldType($field) {
    if (isset($this->schema->$field)) {
      $field = $this->schema->$field;
      return $field['type'];
    }
  }

  public function getFieldLabel($field) {
    if (!isset($this->fields[$field])) {
      return '';
    }
    return tr($this->fields[$field]);
  }

  public function getFieldEditor($field) {
    if (isset($this->editors[$field])) {
      return $this->editors[$field];
    }
    return null;
  }
  
  public function setFieldEditor($field, IEditor $editor) {
    $this->editors[$field] = $editor;
  }

  public function isFieldRequired($field) {
    return isset($this->validator->$field)
    AND isset($this->validator->$field->presence)
    AND $this->validator->$field->presence;
  }

  public function isField($field) {
    return isset($this->fields[$field]);
  }
  
  /**
   * Add record to cache for quick retrieval with find() later
   * @param ActiveRecord $record Record
   */
  public function addToCache(ActiveRecord $record) {
    $primaryKey = $this->primaryKey;
    $this->cache[$record->$primaryKey] = $record;
  }
  
  /**
   * Find a record whose primary key matches the parameter
   * @param mixed $primaryKey Value for primary key
   * @return ActiveRecord|false A matching record or false if not found
   */
  public function find($primaryKey) {
    if ($this->aiPrimaryKey AND $primaryKey == 0) {
      return false;
    }
    if (isset($this->cache[$primaryKey])) {
      return $this->cache[$primaryKey];
    }
    $result = $this->dataSource->select()
      ->where($this->primaryKey . ' = ?', $primaryKey)
      ->limit(1)
      ->execute();
    if (!$result->hasRows()) {
      return false;
    }
    $record = new $this->recordClass($this, $result->fetchAssoc(), false);
    $this->cache[$primaryKey] = $record;
    return $record;
  }
  
  /**
   * Check if a record with the specified primary key value exists
   * @param mixed $primaryKey Value for primary key
   * @return boolean True if a record exists, false if not
   */
  public function exists($primaryKey) {
    if ($this->aiPrimaryKey AND $primaryKey == 0) {
      return false;
    }
    if (isset($this->cache[$primaryKey])) {
      return true;
    }
    $query = SelectQuery::create()
      ->where($this->primaryKey . ' = ?', $primaryKey);
    return $this->dataSource->count($query) > 0;
  }

  public function all(SelectQuery $query = null) {
    if (!isset($query)) {
      $query = SelectQuery::create();
    }
    $result = $this->dataSource->select($query);
    $allArray = array();
    while ($assoc = $result->fetchAssoc()) {
      $allArray[] = new $this->recordClass($this, $assoc, false);
    }
    return $allArray;
  }

  public function first(SelectQuery $query = null) {
    if (!isset($query)) {
      $query = SelectQuery::create();
    }
    $query->limit(1);
    $result = $this->dataSource->select($query);
    if (!$result->hasRows()) {
      return false;
    }
    return new $this->recordClass($this, $result->fetchAssoc(), false);
  }
  public function last(SelectQuery $query = null) {
    if (!isset($query)) {
      $query = SelectQuery::create();
    }
    $query->reverseOrder()->limit(1);
    $result = $this->dataSource->select($query);
    if (!$result->hasRows()) {
      return false;
    }
    return new $this->recordClass($this, $result->fetchAssoc(), false);
  }
  public function count(SelectQuery $query = null) {
    return $this->dataSource->count($query);
  }

  /**
   * Get encoder of a field
   * @param string $field Field name
   * @return Encoder|null An encoder object or null if not set
   */
  public function getEncoder($field) {
    if (isset($this->encoders[$field])) {
      return $this->encoders[$field];
    }
    return null;
  }

  /**
   * Set the encoder of a field
   * @param string $field Field name
   * @param Encoder $encoder Encoder object
   */
  public function setEncoder($field, Encoder $encoder = null) {
    $this->encoders[$field] = $encoder;
  }
}

/**
 * A model is invalid
 * @package Core\Database
 */
class InvalidModelException extends Exception {}

/**
 * An ActiveRecord is invalid
 * @package Core\Database
 */
class InvalidActiveRecordException extends Exception { }

/**
 * A model method was not found
 * @package Core\Database
 */
class ModelMethodNotFoundException extends Exception { }

/**
 * A model was not found
 * @package Core\Database
 */
class ModelNotFoundException extends Exception { }

/**
 * A data source was not found
 * @package Core\Database
 */
class DataSourceNotFoundException extends Exception { }