<?php
include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core/Models');
Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');

interface IModel {
  /**
   * Create a new record of model
   * @param array $data Data for record
   * @param string[] $allowedFields If set, only allow setting these fields
   * @return IRecord New record
   */
  public function create($data = array(), $allowedFields = null);
  
  /**
   * Get name of record/model if applicable
   * @return string Name
   */
  public function getName();
  
  /**
   * Get a list of fields in model
   * @return string[] An array containing field names
  */
  public function getFields();
  
  /**
   * Get the type of a field
   * @param string $field Field name
   * @return string Field type
   */
  public function getFieldType($field);

  /**
   * Get the label of a field
   * @param string $field Field name
   * @return string Field label
   */
  public function getFieldLabel($field);

  /**
   * Get an editor associated with a field
   * @param string $field Field name
   * @return IEditor|null Editor if it exists, null otherwise
   */
  public function getFieldEditor($field);

  /**
   * Whether a field is required
   * @param string $field Field name
   * @return bool True if required, false otherwise
   */
  public function isFieldRequired($field);
  
  /**
   * Whether a field exists
   * @param string $field Field name
   * @return bool True if it does, false otherwise
   */
  public function isField($field);
  
  public function all(SelectQuery $query = null);
  public function first(SelectQuery $query = null);
  public function last(SelectQuery $query = null);
  public function count(SelectQuery $query = null);
}

interface IRecord {
  public function __get($field);
  public function __set($field, $value);
  public function addData($data, $allowedFields = null);
  public function getModel();
  public function save($options = array());
  public function delete();
  public function isNew();
  public function isSaved();
  public function isValid();
  public function getErrors();
  public function encode($field, $options = array());
}

class ActiveModel implements IModel {

  /**
   * @var string
   */
  private $recordClass;
  
  /**
   * @var IDataSource
   */
  private $dataSource;
  
  private $table;
  
  private $schema;
  
  private $fields = array();
  
  private $defaults = array();
  
  private $columns = array();
  
  private $primaryKey;
  
  private $editors = array();

  private $encoders = array();
  
  private $validator;
  
  private $associations;
  
  public final function __construct($recordClass, IDataSource $dataSource, $config = array()) {
    if (!is_subclass_of($recordClass, 'ActiveRecord')) {
      throw new Exception(tr('Invalid record class, must extend ActiveRecord'));
    }
    $this->recordClass = $recordClass;
    $this->dataSource = $dataSource;
    $this->table = $dataSource->getName();
    $this->schema = $dataSource->getSchema();
    $this->columns = $this->schema->getColumns();
    $this->primaryKey = $this->schema->getPrimaryKey();
    
    $recordObj = new $recordClass($this, null);
    $settings = $recordObj->getModelSettings(); 
    
    $validateArray = $settings['validate'];
    $this->validator = $this->createValidator($validateArray);
    
    $this->fields = $settings['fields'];
    $this->defaults = $settings['defaults'];
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
    foreach (array('hasOne', 'belongsTo') as $associationType) {
      foreach ($settings[$associationType] as $class => $options) {
        $this->createAssociation($associationType, 'get', $class, $options);
        $this->createAssociation($associationType, 'set', $class, $options);
      }
    }
    foreach (array('hasMany', 'hasAndBelongsToMany') as $associationType) {
      foreach ($settings[$associationType] as $class => $options) {
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
    if (!isset($options['thisKey'])) {
      $options['thisKey'] = strtolower($this->recordClass) . '_id';
    }
    if (!isset($options['otherKey'])) {
      $options['otherKey'] = strtolower($otherClass) . '_id';
    }
    if ($type == 'hasAndBelongsToMany' AND !isset($options['join'])) {
      if (strcmp($this->table, self::$models[$otherClass]['table']) < 0) {
        $options['join'] = $this->table . '_'
          . self::$models[$otherClass]['table'];
      }
      else {
        $options['join'] = self::$models[$otherClass]['table'] . '_'
          . $this->table;
      }
      // if table does not exist
    }
    $association = null;
    if ($type == 'hasMany' OR $type == 'hasAndBelongsToMany') {
      if (!isset($options['plural'])) {
        $options['plural'] = $class . 's';
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
        //           $options['connection'] = 'this';
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
      case 'dataSource':
      case 'primaryKey':
      case 'validator':
      case 'columns':
      case 'associations':
        return $this->$property;
    }
  }
  
  public function __call($function, $parameters) {
    if (substr($function, 0, 6) == 'findBy') {
      $field = str_replace('-', '_',
        Utilities::camelCaseToDashes(substr($function, 6))
      );
      if ($this->isField($field)) {
        return $this
          ->all(SelectQuery::create()->where($field . ' = ?', $parameters[0]));
      }
    }
    throw new RecordMethodNotFoundException(tr(
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
      return tr(ucfirst($field));
    }
    return tr($this->fields[$field]);
  }

  public function getFieldEditor($field) {
    if (isset($this->editors[$field])) {
      return $this->editors[$field];
    }
    return null;
  }

  public function isFieldRequired($field) {
    return isset($this->validator->$field)
      AND isset($this->validator->$field->presence)
      AND $this->validator->$field->presence;
  }

  public function isField($field) {
    return isset($this->fields[$field]);
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
class DatabaseNotConnectedException extends Exception {}
class TableNotFoundException extends Exception {}

class RecordPropertyNotFoundException extends Exception {}
class RecordMethodNotFoundException extends Exception {}

abstract class ActiveRecord implements IRecord {
  
  private $model;
  private $saved = false;
  private $new = true;
  private $deleted = false;
  private $errors = array();
  private $data = array();
  
  protected $validate = array();
  
  protected $defaults = array();
  
  protected $virtuals = array();
  
  protected $fields = null;
  
  protected $hasOne = array();
  protected $hasMany = array();
  protected $belongsTo = array();
  protected $hasAndBelongsToMany = array();
  
  public final function __construct(ActiveModel $model, $data = array(), $new = true) {
    $this->model = $model;
    $this->new = $new;
    if (!$new) {
      $this->saved = true;
    }
    if (!isset($data)) {
      return;
    }
    foreach ($this->model->columns as $column) {
      if (isset($data[$column])) {
        $this->data[$column] = $data[$column];
      }
      else {
        $this->data[$column] = null;
      }
    }
  }
  
  public function getModelSettings() {
    return array(
      'validate' => $this->validate,
      'defaults' => $this->defaults,
      'virtuals' => $this->virtuals,
      'fields' => $this->fields,
      'hasOne' => $this->hasOne,
      'hasMany' => $this->hasMany,
      'belongsTo' => $this->belongsTo,
      'hasAndBelongsToMany' => $this->hasAndBelongsToMany,
    );
  }
  
  public function __get($field) {
    if (array_key_exists($field, $this->data)) {
      return $this->data[$field];
    }
    else {
      throw new RecordPropertyNotFoundException(tr(
        'Field "%1" was not found in active record "%2".',
        $field, get_class($this)
      ));
    }
  }
  public function __set($field, $value) {
    if (array_key_exists($field, $this->data)
      AND $field != $this->model->primaryKey) {
      $this->data[$field] = $value;
      $this->saved = false;
    }
    else {
      throw new RecordPropertyNotFoundException(tr(
        'Field "%1" was not found in active record "%2".',
        $field, get_class($this)
      ));
    }
  }
  public function __call($method, $parameters) {
    if (isset($this->model->associations[$method])) {
      $association = $this->model->associations[$method];
      if (!isset($parameters[0])) {
        $parameters[0] = null;
      }
      return call_user_func(array($this, $association[0]), $association[1],
        $parameters[0]
      );
    }
    throw new RecordMethodNotFoundException(tr(
      'Method "%1" was not found in active record "%2".',
      $method, get_class($this)
    ));
  }
  
  public function addData($data, $allowedFields = null) {
    if (!is_array($data)) {
      return;
    }
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      if ($this->model->isField($field)) {
        $editor = $this->model->getFieldEditor($field);
        if (isset($editor)) {
          $format = $editor->getFormat();
          $this->$field = $format->toHtml($data[$field]);
        }
        else {
          $this->$field = $data[$field];
        }
      }
    }
  }
  
  /**
   * @return ActiveModel The model associated with this record
   */
  public function getModel() {
    return $this->model;
  }
  
  protected function beforeSave($options) {}
  protected function afterSave($options) {}
  
  public function save($options = array()) {
    if ($this->deleted) {
      return false;
    }
    $defaultOptions = array('validate' => true);
    $options = array_merge($defaultOptions, $options);
    $this->beforeSave($options);
    if ($options['validate'] AND !$this->isValid()) {
      return false;
    }
    if ($this->saved) {
      return true;
    }
    foreach ($this->virtuals as $tasks) {
      if (isset($tasks['presave'])) {
        call_user_func(array($this, $tasks['presave']));
      }
    }
    if ($this->new) {
      $query = $this->model->dataSource->insert();
      $this->data[$this->model->primaryKey] = $query->addPairs($this->data)
        ->execute();
    }
    else {
      $query = $this->model->dataSource->update();
      foreach ($this->data as $column => $value) {
        $query->set($column, $value);
      }
      $query->where($this->model->primaryKey. ' = ?');
      $query->addVar($this->data[$this->model->primaryKey]);
      $query->execute();
    }
    $this->new = false;
    $this->saved = true;
    $this->afterSave($options);
    foreach ($this->virtuals as $tasks) {
      if (isset($tasks['save'])) {
        call_user_func(array($this, $tasks['save']));
      }
    }
    return true;
  }
  public function delete() {
    $this->model->dataSource->delete()
      ->where($this->model->primaryKey . ' = ?')
      ->addVar($this->data[$this->model->primaryKey])->execute();
    $this->deleted = true;
  }

  public function isNew() {
    return $this->new;
  }
  public function isSaved() {
    return $this->saved;
  }
  protected function validateValue($column, $value, $conditionKey,
    $conditionValue) {
    $validate = array();
    if ($conditionValue instanceof ValidatorRule) {
      foreach ($conditionValue->getRules() as $subConditionKey => $subConditionValue) {
        $validate = $this
          ->validateValue($column, $value, $subConditionKey, $subConditionValue);
        if (!$validate) {
          return false;
        }
      }
      return true;
    }
    if ($conditionKey != 'presence' AND $conditionKey != 'null'
      AND empty($value) AND !is_numeric($value)) {
      return true;
    }
    switch ($conditionKey) {
      case 'presence':
        return (!empty($value) OR is_numeric($value)) == $conditionValue;
      case 'null':
        return is_null($value) == $conditionValue;
      case 'email':
        return preg_match(
        "/^[a-z0-9.!#$%&*+\/=?^_`{|}~-]+@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i",
        $value) == 1;
      case 'url':
        return preg_match("/^https?:\/\/[-a-z0-9@:%_\+\.~#\?&\/=\[\]]+$/i",
        $value) == 1;
      case 'minLength':
        return strlen($value) >= $conditionValue;
      case 'maxLength':
        return strlen($value) <= $conditionValue;
      case 'isNumeric':
        return is_numeric($value) == $conditionValue;
      case 'isInteger':
        return (preg_match('/^[-+]?\d+$/', $value) == 1) == $conditionValue;
      case 'isFloat':
        return (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $value)
        == 1) == $conditionValue;
      case 'isBoolean':
        return ($value == 1 OR $value == 0) == $conditionValue;
      case 'minValue':
        $value = is_float($conditionValue) ? (float) $value : (int) $value;
        return $value >= $conditionValue;
      case 'maxValue':
        $value = is_float($conditionValue) ? (float) $value : (int) $value;
        return $value <= $conditionValue;
      case 'match':
        return preg_match($conditionValue, $value) == 1;
      case 'unique':
        $query = $this->model->dataSource->select()->limit(1);
        if (!$this->isNew()) {
          $query->where($this->model->primaryKey . ' != ? AND ' . $column . ' = ?')
            ->addVar($this->data[$this->model->primaryKey]);
        }
        else {
          $query->where($column . ' = ?');
        }
        $result = $query->addVar($value)->execute();
        return $result->hasRows() != $conditionValue;
      case 'callback':
        return !is_callable(array($this, $conditionValue))
        OR call_user_func(array($this, $conditionValue), $value);
    }
    return true;
  }
  
  protected function getMessage($rule, $value) {
    if ($value instanceof ValidatorRule) {
      return tr($value->message);
    }
    switch ($rule) {
      case 'presence':
        return $value ? tr('Must not be empty.') : tr('Must be empty.');
      case 'null':
        return $value ? tr('Must be null.') : tr('Must not be null.');
      case 'email':
        return tr('Not a valid email address.');
      case 'url':
        return tr('Not a valid URL.');
      case 'minLength':
        return trn('Minimum length of %1 character.',
          'Minimum length of %1 characters.', $value
        );
      case 'maxLength':
        return trn('Maximum length of %1 character.',
          'Maximum length of %1 characters.', $value
        );
      case 'isNumeric':
        return $value ? tr('Must be numeric.') : tr('Must not be numeric.');
      case 'isInteger':
        return $value ? tr('Must be an integer.')
          : tr('Must not be an integer.');
      case 'isFloat':
        return $value ? tr('Must be a decimal number.')
          : tr('Must not be a decimal number.');
      case 'isBoolean':
        return $value ? tr('Must be boolean (1 or 0).')
          : tr('Must not be boolean.');
      case 'minValue':
        return tr('Minimum value of %1.', $value);
      case 'maxValue':
        return tr('Maximum value of %1.', $value);
      case 'unique':
        return $value ? tr('Must be unique.') : tr('Must not be unique.');
      case 'match':
      case 'callback':
      default:
        return tr('Invalid value.');
    }
  }
  
  protected function beforeValidate() {}
  
  public function isValid() {
    $this->errors = array();
    $this->beforeValidate();
    $validator = $this->model->validator;
    foreach ($this->data as $column => $value) {
      if (!is_scalar($value) AND !is_null($value)) {
        $this->errors[$column] = tr('Value not a scalar.');
      }
      if (!isset($validator->$column)) {
        continue;
      }
      foreach ($validator->$column->getRules() as $conditionKey => $conditionValue) {
        $validate = $this->validateValue($column, $value, $conditionKey, $conditionValue);
        if (!$validate) {
          $this->errors[$column] = $this
            ->getMessage($conditionKey, $conditionValue);
          break;
        }
      }
    }
    $this->afterValidate();
    if (count($this->errors) < 1) {
      return true;
    }
    return false;
  }
  
  protected function afterValidate() {}
  
  public function getErrors() {
    return $this->errors;
  }
  
  public function encode($field, $options = array()) {
    if ($this->model->isField($field)) {
      $text = $this->$field;
      $encoder = $this->model->getEncoder($field);
      if (isset($encoder)) {
        return $encoder->encode($text, $options);
      }
      else {
        return h($text);
      }
    }
  }
}

class User extends ActiveRecord {
  protected $hasMany = array(
    'Post' => array('thisKey' => 'user_id'),
    'Comment' => array('thisKey' => 'user_id'),
  );

  protected $belongsTo = array(
    'Group' => array('otherKey' => 'group_id'),
  );

  protected $validate = array(
    'username' => array('presence' => true,),
    'password' => array('presence' => true,),
    'email' => array('presence' => true, 'email' => true),
    'confirm_password' => array(
      'presence' => true,
      'ruleConfirm' => array(
        'callback' => 'confirmPassword',
        'message' => 'The two passwords are not identical'
      ),
    ),
  );

  protected $fields = array(
    'username' => 'Username',
    'email' => 'Email',
    'password' => 'Password',
    'confirm_password' => 'Confirm password',
  );

  protected $defaults = array(
    'cookie' => '',
    'session' => '',
    'ip' => '',
    'group_id' => 0,
  );
}

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

$dataSource = $db->users;

$model = new ActiveModel('User', $dataSource);

$user = $model->first();

header('Content-Type:text/plain');

var_dump($user->getComments());

var_dump(Logger::getLog());
