<?php
/**
 * A generic form
 * @TODO rename: FormBuilder ??
 * @package Jivoo\Models
 */
class Form implements IBasicRecord, IBasicModel {
  
  /**
   * @var array Associative array of data
   */
  private $data = array();
  
  /**
   * @var array Associative array of field names and error messages
   */
  private $errors = array();

  /**
   * @var string Form name
   */
  private $name;
  
  /**
   * @var Associative array of field names and information (label, type and
   * required)
   */
  private $fields = array();

  
  private $validator;

  /**
   * Constructor.
   * @param string $name Form name
   * @param array $data Associative array of data
   */
  public function __construct($name, $data = array()) {
    $this->name = $name;
    $this->data = $data;
    $this->validator = new Validator($this);
  }

  public function getValidator() {
    return $this->validator;
  }

  /* IRecord implementation */

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

  public function addData($data, $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      if (isset($this->fields[$field])) {
        $this->data[$field] = $value;
      }
    }
  }
  
  public function getData() {
    return $this->data;
  }
  
  /**
   * @return Form Model
   */
  public function getModel() {
    return $this;
  }

  public function isValid() {
    foreach ($this->fields as $field => $options) {
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

  /**
   * Add a field to form
   * @param string $field Field name
   * @param string $type Type of field, e.g. 'string', 'text', 'date', 'hidden'
   * @param string $label Field label, if not set the field name will be used
   * @param bool $required Whether or not the field is required
   */
  public function addField($field, $type = 'string', $label = null,
                           $required = true) {
    if (!isset($label)) {
      $label = tr(ucfirst($field));
    }
    $this->fields[$field] = array('label' => $label, 'type' => $type,
      'required' => $required
    );
  }

  /**
   * Add a string field to form
   * @param string $field Field name
   * @param string $label Field label, if not set the field name will be used
   * @param bool $required Whether or not the field is required
   */
  public function addString($field, $label = null, $required = true) {
    $this->addField($field, 'string', $label, $required);
  }

  /**
   * Add a text field to form
   * @param string $field Field name
   * @param string $label Field label, if not set the field name will be used
   * @param bool $required Whether or not the field is required
   */
  public function addText($field, $label = null, $required = true) {
    $this->addField($field, 'text', $label, $required);
  }
  
  /**
   * Add an error
   * @param string $field Field name
   * @param string $errorMsg Error message
   */
  public function addError($field, $errorMsg) {
    $this->errors[$field] = $errorMsg;
  }

  /* IModel implementation */

  /**
   * @return Form New form from this model
   */
  public function create($data = array(), $allowedFields = null) {
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    return new Form($this, $data);
  }

  public function getName() {
    return $this->name;
  }

  public function getFields() {
    return array_keys($this->fields);
  }

  public function getType($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['type'];
    }
  }

  public function getLabel($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['label'];
    }
  }

  public function getEditor($field) {
    return null;
  }

  public function isRequired($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field]['required'];
    }
  }

  public function hasField($field) {
    return isset($this->fields[$field]);
  }

  public function offsetExists($field) {
    return $this->__isset($field);
  }

  public function offsetGet($field) {
    return $this->__get($field);
  }

  public function offsetSet($field, $value) {
    $this->__set($field, $value);
  }

  public function offsetUnset($field) {
    $this->__unset($field);
  }

}
