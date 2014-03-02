<?php
class ActiveRecord implements IRecord {
  
  private $data = array();
  
  private $updatedData = array();
  /**
   * @var ActiveModel
   */
  private $model;
  private $errors = array();
  private $new = false;
  private $saved = true;

  private $associations = array();

  private final function __construct(ActiveModel $model, $data = array(), $allowedFields = null) {
    $this->model = $model;
    $this->data = array_fill_keys($model->getFields(), null);
    $this->addData($data, $allowedFields);
    $this->associations = $this->model->getAssociations();
  }

  public static function createNew(ActiveModel $model, $data = array(), $allowedFields = null, $class = null) {
    if (isset($class))
      $record = new $class($model, $data, $allowedFields);
    else
      $record = new ActiveRecord($model, $data, $allowedFields);
    $record->new = true;
    $record->saved = false;
    return $record;
  }
  
  public static function createExisting(ActiveModel $model, $data = array(), $class = null) {
    if (isset($class))
      $record = new $class($model, $data);
    else
      $record = new ActiveRecord($model, $data);
    $record->updatedData = array();
    return $record;
  }

  public function getModel() {
    return $this->model;
  }
  
  public function addData($data, $allowedFields = null) {
    if (!is_array($data)) {
      return;
    }
    if (!isset($allowedFields))
      $allowedFields = $this->data;
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      $this->__set($field, $data[$field]);
    }
  }

  public function __get($field) {
    if (isset($this->associations[$field])) {
      if (!is_array($this->associations[$field]))
        $this->associations[$field] = $this->model->getAssociation($this, $this->associations[$field]);
      return $this->associations[$field];
    }
    if (!array_key_exists($field, $this->data))
      throw new InvalidRecordFieldException(tr('"%1" is not a valid field', $field));
    return $this->data[$field];
  }

  public function __set($field, $value) {
    if (isset($this->associations[$field])) {
      return $this->model->setAssociation($this, $this->associations[$field], $value);
    }
    else {
      if (!array_key_exists($field, $this->data))
        throw new InvalidRecordFieldException(tr('"%1" is not a valid field', $field));
      $this->data[$field] = $value;
      $this->updatedData[$field] = $value;
      $this->saved = false;
    }
  }

  public function __isset($field) {
    if (isset($this->associations[$field]))
      return $this->model->hasAssociation($this, $this->associations[$field]);
    if (!array_key_exists($field, $this->data))
      throw new InvalidRecordFieldException(tr('"%1" is not a valid field', $field));
    return isset($this->data[$field]);
  }

  public function __unset($field) {
    if (isset($this->associations[$field]))
      return $this->model->unsetAssociation($this, $this->associations[$field]);
    if (!array_key_exists($field, $this->data))
      throw new InvalidRecordFieldException(tr('"%1" is not a valid field', $field));
    $this->data[$field] = null;
    $this->updatedData[$field] = null;
    $this->saved = false;
  }

  public function __call($method, $parameters) {
    $method = 'record' . ucfirst($method);
    $function = array($this->model, $method);
    if (function_exists($function))
      return call_user_func_array($function, $parameters);
    throw new InvalidMethodException(tr('"%1" is not a valid method', $method));
  }

  public function set($field, $value) {
    $this->__set($field, $value);
    return $this;
  }
  
  public function isSaved() {
    return $this->saved;
  }
  
  public function isNew() {
    return $this->new;
  }
  
  public function getErrors() {
    return $this->errors;
  }
  
  public function isValid() {
    $validator = $this->model->getValidator();
    $this->errors = $validator->validate($this);
    return count($this->errors) == 0;
  }
  
  public function save($options = array()) {
    $defaultOptions = array('validate' => true);
    $options = array_merge($defaultOptions, $options);
    if ($options['validate'] AND !$this->isValid())
      return false;
    if ($this->isNew()) {
      $this->model->insert($this->data);
      $this->new = false;
    }
    else if (count($this->updatedData) > 0) {
      $this->model->selectRecord($this)->set($this->updatedData)->update();
    }
    $this->updatedData = array();
    $this->saved = true;
    return true;
  }
  
  public function delete() {
    $this->model->selectRecord($this)->delete();
  }

}

class InvalidMethodException extends Exception { }
