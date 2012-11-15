<?php

class Form implements IModel {
  private $name;
  private $fields = array();
  private $data = array();
  private $errors = array();

  public function __construct($name) {
    $this->name = $name;
  }
  
  public function addData($data) {
    foreach ($data as $field => $value) {
      if (isset($this->fields[$field])) {
        $this->data[$field] = $value;
      }
    }
  }
  
  public function addField($field, $type = 'string', $label = null, $required = true) {
    if (!isset($label)) {
      $label = tr(ucfirst($field));
    }
    $this->fields[$field] = array(
      'label' => $label,
      'type' => $type,
      'required' => $required
    );
  }
  
  public function addString($field, $label = null, $required = true) {
    $this->addField($field, 'string', $label, $required);
  }
  
  public function addText($field, $label = null, $required = true) {
    $this->addField($field, 'text', $label, $required);
  }
  
  public function addError($field, $errorMsg) {
    $this->errors[$field] = $errorMsg;
  }
  
  /* IModel implementation */

  public function __get($field) {
    if (isset($this->data[$field])) {
      return $this->data[$field];
    }
    return null;
  }

  public function __set($field, $value) {
    if (isset($this->fields[$field])) {
      $this->data[$field] = $value;
    }
  }

  public function __isset($field) {
    return isset($this->data[$field]);
  }

  public function getName() {
    return $this->name;
  }

  public function getFields() {
    return array_keys($this->fields);
  }
  
  public function getFieldType($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['type'];
    }
  }
  
  public function getFieldLabel($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['label'];
    }
  }

  public function getFieldEditor($field) {
    return null;
  }
  
  public function isFieldRequired($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['required'];
    }
  }
  
  public function isField($field) {
    return isset($this->fields[$field]);
  }
 
  public function isValid() {
    foreach ($this->fields as $field => $options) {
      if ($options['required'] AND empty($this->data[$field]) AND !is_numeric($this->data[$field])) {
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
