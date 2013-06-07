<?php
class ActiveModel implements IModel {

  /**
   * @var string
   */
  private $recordClass;

  /**
   * @var IDataSource
   */
  private $dataSource;
  
  private $otherModels;
  
  private $otherSources;

  private $table;

  private $schema;

  private $fields = array();

  private $defaults = array();

  private $columns = array();

  private $primaryKey;

  private $editors = array();

  private $encoders = array();
  
  private $cache = array();

  private $validator;

  private $settings;
  
  private $associations;

  public final function __construct($recordClass, IDataSource $dataSource,
                                    IDictionary $otherModels = null,
                                    IDictionary $otherSources = null,
                                    $config = array()) {
    if (!is_subclass_of($recordClass, 'ActiveRecord')) {
      throw new InvalidActiveRecordException(
        tr('Invalid record class, must extend ActiveRecord')
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
    $this->primaryKey = $this->schema->getPrimaryKey();

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

  private function createAssociations() {
    foreach (array('hasOne', 'belongsTo') as $associationType) {
      foreach ($this->settings[$associationType] as $class => $options) {
        if (is_string($options)) {
          $class = $options;
          $options = array();
        }
        $this->createAssociation($associationType, 'get', $class, $options);
        $this->createAssociation($associationType, 'set', $class, $options);
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
      }
    }
    if (isset($association)) {
      $this->associations[$method . $class] = $association;
    }
  }

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
        return $this->$property;
    }
  }

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
  
  public function addToCache(ActiveRecord $record) {
    $primaryKey = $this->primaryKey;
    $this->cache[$record->$primaryKey] = $record;
  }
  
  public function find($primaryKey) {
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
  
  public function exists($primaryKey) {
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

  public function getEncoder($field) {
    if (isset($this->encoders[$field])) {
      return $this->encoders[$field];
    }
    return null;
  }

  public function setEncoder($field, Encoder $encoder = null) {
    $this->encoders[$field] = $encoder;
  }
}

class InvalidModelException extends Exception {}
class InvalidActiveRecordException extends Exception { }
class ModelMethodNotFoundException extends Exception { }
class ModelNotFoundException extends Exception { }
class DataSourceNotFoundException extends Exception { }