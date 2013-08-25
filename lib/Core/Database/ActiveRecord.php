<?php
/**
 * A record of an {@see ActiveModel}
 * @package Core\Database
 */
abstract class ActiveRecord implements IRecord {

  /**
   * @var ActiveModel Associated model
   */
  private $model;
  
  /**
   * @var bool Whether or not the current data has been saved
   */
  private $saved = false;
  
  /**
   * @var bool Whether or not the current record is new (not saved yet)
   */
  private $new = true;
  
  /**
   * @var bool Whether or not the current record has been deleted
   */
  private $deleted = false;
  
  /**
   * @var array An associative array of field names and associated errors if any
   */
  private $errors = array();
  
  /**
   * @var array An associative array of field names and values
   */
  private $data = array();
  
  /**
   * @var array An associative array of virtual field names and values
   */
  private $virtualData = array();
  
  /**
   * @var string Name of table if non-trivial. Normally the table name is
   * computed by adding an 's' to the lowercase record name, e.g.
   * 'User' -> 'users'. This doesn't work for words like 'Category' in which
   * case the table name should be set manually. 
   */
  protected $table = null;

  /**
   * @var array An associative array of field names and validation rules, see
   * {@see Validator}
   */
  protected $validate = array();

  /**
   * @var array An associative array of field names and default values
   */
  protected $defaults = array();

  /**
   * @var array An associative array of virtual field names and settings
   */
  protected $virtuals = array();

  /**
   * @var array An associative array of field names and labels
   */
  protected $fields = null;

  /**
   * @var array An associative array of 'hasOne'-associations. Each association
   * creates the following methods (using Post model as example):
   * * `Post getPost()` Get associated record
   * * `setPost(Post $record)` Set associated record
   * * `removePost()` Unset associated record
   */
  protected $hasOne = array();

  /**
   * @var array An associative array of 'hasMany'-associations. Each association
   * creates the following methods (using Post model as example):
   * * `Post[] getPosts(SelectQuery $customSelect = null)` Get associated records,
   *   optionally matching a custom select query
   * * `int countPosts(SelectQuery $customSelect = null)` Get number of associated records,
   *   optionally matching a custom select query
   * * `bool hasPost(Post $record)` Check whether or not a record is associated
   *   with this one
   * * `addPost(Post $record)` Add another record
   * * `removePost(Post $record)` Remove an associated record
   */
  protected $hasMany = array();

  /**
   * @var array An associative array of 'belongsTo'-associations. Each association
   * creates the following methods (using Post model as example):
   * * `Post getPost()` Get associated record
   * * `setPost(Post $record)` Set associated record
   * * `removePost()` Unset associated record
   */
  protected $belongsTo = array();

  /**
   * @var array An associative array of 'hasAndBelongsToMany'-associations.
   * Each association creates the following methods (using Post model as example):
   * * `Post[] getPosts(SelectQuery $customSelect = null)` Get associated records,
   *   optionally matching a custom select query
   * * `int countPosts(SelectQuery $customSelect = null)` Get number of associated records,
   *   optionally matching a custom select query
   * * `bool hasPost(Post $record)` Check whether or not a record is associated
   *   with this one
   * * `addPost(Post $record)` Add another record
   * * `removePost(Post $record)` Remove an associated record
   */
  protected $hasAndBelongsToMany = array();

  /**
   * Constructor.
   * @param ActiveModel $model The associated model
   * @param array $data An associative array of field names and values.
   * @param bool $new Whether or not the record is new, i.e. not saved yet 
   */
  public final function __construct(ActiveModel $model = null, $data = array(), $new = true) {
    $this->model = $model;
    $this->new = $new;
    if (!is_array($data) OR !isset($model)) {
      return;
    }
    $allFields = array_unique(array_merge(
      $this->model->columns,
      $this->model->getFields()
    ));
    foreach ($allFields as $field) {
      if (isset($data[$field])) {
        $this->data[$field] = $data[$field];
      }
      else {
        $this->data[$field] = null;
      }
    }
    if (!$new) {
      $this->saved = true;
      $this->addToCache();
    }
  }
  
  /**
   * Add record to cache for quick retrieval usign {@see ActiveModel::find()}
   * later
   */
  public function addToCache() {
    $this->model->addToCache($this);
  }

  /**
   * Get the settings defined by the record class
   * @return array An associative array of settings
   */
  public function getModelSettings() {
    return array(
      'table' => $this->table,
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

  /**
   * Get value of property
   * @throws RecordPropertyNotFoundException if the property doesn't exist
   */
  public function __get($field) {
    if (isset($this->virtuals[$field])
    AND isset($this->virtuals[$field]['get'])) {
      return call_user_func(array($this, $this->virtuals[$field]['get']));
    }
    else if (array_key_exists($field, $this->data)) {
      return $this->data[$field];
    }
    else {
      throw new RecordPropertyNotFoundException(tr(
        'Field "%1" was not found in active record "%2".',
        $field, get_class($this)
      ));
    }
  }
  
  /**
   * Set value of property
   * @throws RecordPropertyNotFoundException if the property doesn't exist
   */
  public function __set($field, $value) {
    if (isset($this->virtuals[$field])
    AND isset($this->virtuals[$field]['set'])) {
      call_user_func(array($this, $this->virtuals[$field]['set']), $value);
      $this->saved = false;
    }
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
  
  public function __isset($field) {
    return isset($this->data[$field]) OR isset($this->virtuals[$field]);
  }
  
  /**
   * Call an association-method. These are created based on the association
   * settings
   * @param string $method Method name
   * @param mixed[] $parameters List of parameters
   * @throws RecordMethodNotFoundException if method doesn't exist
   * @return mixed Result of method
   */
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

  /**
   * Called before saving record
   * @param array $options An associative array of keys and values
   */
  protected function beforeSave($options) {}
  
  /**
   * Called after saving record
   * @param array $options An associative array of keys and values
   */
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
      $data = array_intersect_key($this->data, array_flip($this->model->columns));
      $this->data[$this->model->primaryKey] = $query->addPairs($data)
      ->execute();
    }
    else {
      $query = $this->model->dataSource->update();
      foreach ($this->model->columns as $column) {
        $query->set($column, $this->$column);
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

  /**
   * Called before deleting record
   */
  protected function beforeDelete() {}
  
  /**
   * Called after deleting record
   */
  protected function afterDelete() {}
  
  public function delete() {
    $this->beforeDelete();
    $this->model->dataSource->delete()
      ->where($this->model->primaryKey . ' = ?')
      ->addVar($this->data[$this->model->primaryKey])
      ->execute();
    $this->deleted = true;
    $this->afterDelete();
  }

  public function isNew() {
    return $this->new;
  }
  
  public function isSaved() {
    return $this->saved;
  }
  
  /**
   * Validate a field
   * @param string $column Field name
   * @param string $conditionKey The type of validation rule, e.g. 'presence' or
   * 'url'
   * @param mixed $conditionValue The value to validate against
   * @return boolean True if field is valid, false otherwise
   */
  protected function validateField($column, $conditionKey, $conditionValue) {
    $validate = array();
    $value = $this->$column;
    if ($conditionValue instanceof ValidatorRule) {
      foreach ($conditionValue->getRules() as $subConditionKey => $subConditionValue) {
        $validate = $this
        ->validateField($column, $subConditionKey, $subConditionValue);
        if (!$validate) {
          return false;
        }
      }
      return true;
    }
    if ($conditionKey != 'presence' AND $conditionKey != 'null'
      AND $conditionKey != 'callback'
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
      case 'date':
        if (preg_match('/^[-+]?\d+$/', $value) == 1) {
          $timestamp = (int)$value;
        }
        else {
          $timestamp = strtotime($value);
          if ($timestamp === false) {
            return !$conditionValue;
          }
        }
        if (!$conditionValue) {
          return false;
        }
        $this->$column = $timestamp;
        return true;
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

  /**
   * Get message for a validation rule
   * @param string $rule Validation rule type
   * @param mixed $value The value to compare validation agains
   * @return string A translated message
   */
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
      case 'date':
        return tr('Must be a valid date.');
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

  /**
   * Called before validating
   */
  protected function beforeValidate() {}

  public function isValid() {
    $this->errors = array();
    $this->beforeValidate();
    $validator = $this->model->validator;
    foreach ($this->virtuals as $field => $tasks) {
      if (isset($tasks['validate'])) {
        $error = call_user_func(array($this, $tasks['validate']));
        if ($error !== true) {
          $this->errors[$field] = $error;
        }
      }
    }
    foreach ($this->data as $field => $value) {
      if (!is_scalar($value) AND !is_null($value)) {
        $this->errors[$field] = tr('Value not a scalar.');
      }
      if (!isset($validator->$field)) {
        continue;
      }
      foreach ($validator->$field->getRules() as $conditionKey => $conditionValue) {
        $validate = $this->validateField($field, $conditionKey, $conditionValue);
        if (!$validate) {
          $this->errors[$field] = $this
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

  /**
   * Called after validating
   */
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
  
  /// ASSOCIATION METHODS

  /**
   * get-method for a many-association
   * @param array $options Associative array of options
   * @param SelectQuery $customSelect A custom select query
   * @return ActiveRecord[] List of records matching query
   */
  private function manyGet($options, SelectQuery $customSelect = null) {
    $otherModel = $options['model'];
  
    if (!isset($customSelect)) {
      $select = SelectQuery::create();
    }
    else {
      $select = $customSelect;
    }
    if (isset($options['join'])) {
      $select->join(
        $options['join'],
        $otherModel->primaryKey,
        $options['otherKey']
      );
    }
  
    $select->where($options['thisKey'] . ' = ?');
    $select->addVar($this->data[$this->model->primaryKey]);
  
    $result = $otherModel->all($select);
    if (isset($options['count']) AND !isset($customSelect)) {
      $count = count($result);
      if ($this->data[$options['count']] != $count) {
        $this->data[$options['count']] = $count;
        $this->saved = false;
        $this->save();
      }
    }
    return $result;
  }
  
  /**
   * count-method for a many-association
   * @param array $options Associative array of options
   * @param SelectQuery $customSelect A custom select query
   * @return int Number of records matching query
   */
  private function manyCount($options, SelectQuery $customSelect = null) {
    $otherModel = $options['model'];
  
    if (!isset($customSelect)) {
      $select = SelectQuery::create();
    }
    else {
      $select = $customSelect;
    }
    if (isset($options['join'])) {
      $select->join(
        $options['join'],
        $otherModel->primaryKey,
        $options['otherKey']
      );
    }
  
    $select->where($options['thisKey'] . ' = ?');
    $select->addVar($this->data[$this->model->primaryKey]);
  
    $result = $otherModel->count($select);
    if (isset($options['count']) AND !isset($customSelect)) {
      if ($this->data[$options['count']] != $result) {
        $this->data[$options['count']] = $result;
        $this->saved = false;
        $this->save();
      }
    }
    return $result;
  }

  /**
   * has-method for a many-association
   * @param array $options Associative array of options
   * @param ActiveRecord $record Record to check for
   * @return bool Whether or not current record is associated with $record
   */
  private function manyHas($options, ActiveRecord $record) {
    $otherModel = $options['model'];
    $query = SelectQuery::create();
    if (isset($options['join'])) {
      $query->where(
        $options['thisKey'] . ' = ? AND ' . $options['otherKey'] . ' = ?');
      $query->addVar($this->data[$this->model->primaryKey]);
      $query->addVar($record->data[$record->model->primaryKey]);
      return $options['join']->count($query) > 0;
    }
    else {
      $query->where($options['thisKey'] . ' = ?');
      $query->addVar($this->data[$this->model->primaryKey]);
      return $otherModel->count($query) > 0;
    }
  }
  
  /**
   * add-method for a many-association
   * @param array $options Associative array of options
   * @param ActiveRecord $record Record to add
   * @return ActiveRecord This record
   */
  private function manyAdd($options, ActiveRecord $record) {
    if ($this->manyHas($options, $record)) {
      return false;
    }
    
    if ($this->new) {
      return false;
    }
  
    if (isset($options['join'])) {
      if ($record->new) {
        return false;
      }
      $query = $options['join']->insert();
      $query->addPair($options['thisKey'], $this->data[$this->model->primaryKey]);
      $query->addPair($options['otherKey'],
        $record->data[$record->model->primaryKey]
      );
      $query->execute();
    }
    else {
      $record->data[$options['thisKey']] = $this->data[$this->model->primaryKey];
      $record->saved = false;
      if (!$record->new) {
        $record->save();
      }
    }
    return $this;
  }
  
  /**
   * remove-method for a many-association
   * @param array $options Associative array of options
   * @param ActiveRecord $record Record to remove
   * @return ActiveRecord This record
   */
  private function manyRemove($options, ActiveRecord $record) {
  
    if ($this->new) {
      return false;
    }
    
    if (isset($options['join'])) {
      if ($record->new) {
        return false;
      }
      $query = $options['join']->delete();
      $query->where(
        $options['thisKey'] . ' = ? AND ' . $options['otherKey'] . ' = ?'
      );
      $query->addVar($this->data[$this->model->primaryKey]);
      $query->addVar($record->data[$record->model->primaryKey]);
      $query->execute();
    }
    else {
      $record->data[$options['thisKey']] = 0;
      $record->saved = false;
      if (!$record->new) {
        $record->save();
      }
    }
    return $this;
  }
  
  /**
   * get-method for a one-association
   * @param array $options Associative array of options
   * @return ActiveRecord|false Record or null if none associated
   */
  private function oneGet($options) {
    $otherClass = $options['class'];
    if ($options['connection'] == 'this') {
      if (!isset($this->data[$options['otherKey']])) {
        return false;
      }
      return $options['model']->find($this->data[$options['otherKey']]);
    }
    return $options['model']->first(SelectQuery::create()
      ->where($options['thisKey'] . ' = ?', $this->data[$this->primaryKey])
    );
  }
  
  /**
   * set-method for a one-association
   * @param array $options Associative array of options
   * @param ActiveRecord $record Record to set
   * @return ActiveRecord|false This record or false if failed
   */
  private function oneSet($options, ActiveRecord $record) {
    $otherClass = $options['class'];
  
    if ($options['connection'] == 'this') {
      if ($record->new) {
        return false;
      }
      $this->data[$options['otherKey']] = $record->data[$record->model->primaryKey];
      $this->saved = false;
      if (!$this->new) {
        $this->save();
      }
    }
    else {
      if ($this->new) {
        return false;
      }
      $record->data[$options['thisKey']] = $this->data[$this->model->primaryKey];
      $record->saved = false;
      if (!$record->new) {
        $record->save();
      }
    }
    return $this;
  }
  
  /**
   * remove-method for a one-association
   * @param array $options Associative array of options
   * @return ActiveRecord|false This record or false if failed
   */
  private function oneRemove($options) {
    $otherClass = $options['class'];
  
    if ($options['connection'] == 'this') {
      $this->data[$options['otherKey']] = 0;
      $this->saved = false;
      if (!$this->new) {
        $this->save();
      }
    }
    else {
      if ($this->new) {
        return false;
      }
      $record = $options['model']->first(SelectQuery::create()
        ->where($options['thisKey'] . ' = ?', $this->data[$this->primaryKey])
      );
      $record->data[$options['thisKey']] = 0;
      $record->saved = false;
      if (!$record->new) {
        $record->save();
      }
    }
    return $this;
  }
}

/**
 * A record property could not be found
 * @package Core\Database
 */
class RecordPropertyNotFoundException extends Exception {}

/**
 * A record method could not be found
 * @package Core\Database
 */
class RecordMethodNotFoundException extends Exception {}