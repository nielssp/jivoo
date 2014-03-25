<?php
/**
 * A generic form
 * @package Jivoo\Models
 */
class Form implements IRecord {
  /**
   * @var FormModel Model
   */
  private $model;
  
  /**
   * @var array Associative array of data
   */
  private $data = array();
  
  /**
   * @var array Associative array of field names and error messages
   */
  private $errors = array();

  /**
   * Constructor.
   * @param string|FormModel $name Form name or model
   * @param array $data Associative array of data
   */
  public function __construct($name, $data = array()) {
    if ($name instanceof FormModel) {
      $this->model = $name;
    }
    else {
      $this->model = new FormModel($name);
    }
    $this->data = $data;
  }

  public function addError($field, $errorMsg) {
    $this->errors[$field] = $errorMsg;
  }

  /* IRecord implementation */

  public function __get($field) {
    if (isset($this->data[$field])) {
      return $this->data[$field];
    }
    return null;
  }

  public function __set($field, $value) {
    if (isset($this->model->fields[$field])) {
      $this->data[$field] = $value;
    }
  }

  public function __isset($field) {
    return isset($this->data[$field]);
  }

  public function addData($data, $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      if (isset($this->model->fields[$field])) {
        $this->data[$field] = $value;
      }
    }
  }
  
  /**
   * @return FormModel Form model
   */
  public function getModel() {
    return $this->model;
  }
  
  public function save($options = array()) {
    return false;
  }
  
  public function delete() {
  }
  
  public function isNew() {
    return false;
  }
  
  public function isSaved() {
    return true;
  }

  public function isValid() {
    foreach ($this->model->fields as $field => $options) {
      if ($options['required'] AND empty($this->data[$field])
          AND !is_numeric($this->data[$field])) {
        $this->addError($field, tr('Must not be empty.'));
      }
    }
    return count($this->errors) == 0;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function encode($field, $options = array()) {
    if (isset($this->data[$field])) {
      return h($this->data[$field]);
    }
  }
}
