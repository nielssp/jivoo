<?php
class Record implements IRecord {
  
  private $data = array();
  
  private $updatedData = array();
  /**
   * @var IModel
   */
  protected $model;
  
  private $errors = array();
  
  private $new = false;
  private $saved = true;
  
  private function __construct(IModel $model, $data = array(), $allowedFields = null) {
    $this->model = $model;
    $this->addData($data, $allowedFields);
  }
  
  public static function createNew(IModel $model, $data = array(), $allowedFields = null) {
    $record = new Record($model, $data, $allowedFields);
    $record->new = true;
    $record->saved = false;
    return $record;
  }
  
  public static function createExisting(IModel $model, $data = array()) {
    $record = new Record($model, $data);
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
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      $this->$field = $data[$field];
    }
  }
  
  public function __get($field) {
    return $this->data[$field];
  }
  
  public function __set($field, $value) {
    $this->data[$field] = $value;
    $this->updatedData[$field] = $value;
    $this->saved = false;
  }
  
  public function __isset($field) {
    return isset($this->data[$field]);
  }
  
  public function __unset($field) {
    $this->data[$field] = null;
    $this->updatedData[$field] = null;
    $this->saved = false;
  }
  
  public function set($field, $value) {
    $this->$field = $value;
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
