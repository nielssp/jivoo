<?php

class Validator {
  private $fields = array();
  
  public function __construct($fields = array()) {
    foreach ($fields as $field => $rules) {
      $this->fields[$field] = new ValidatorField($rules);
    }
  }
  
  public function __get($field) {
    return $this->get($field);
  }
  
  public function __isset($field) {
    return isset($this->fields[$field]);
  }
  
  public function __unset($field) {
    $this->remove($field);
  }
  
  public function get($field) {
    if (!isset($this->fields[$field])) {
      $this->fields[$field] = new ValidatorField();
    }
    return $this->fields[$field];
  }
  
  public function remove($field) {
    if (isset($this->fields[$field])) {
      unset($this->fields[$field]);
    }
  }
  
  public function getFields() {
    return $this->fields;
  }
}


