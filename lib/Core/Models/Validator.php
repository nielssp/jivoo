<?php
/**
 * A validator
 * @package Core\Models
 * @TODO Some information about $validator-array and use of validators here
 */
class Validator {
  /**
   * @var array Associative array of field names and ValidatorField objects
   */
  private $fields = array();

  /**
   * Constructor
   * @param array $fields Associative array of field-names and rules
   */
  public function __construct($fields = array()) {
    foreach ($fields as $field => $rules) {
      $this->fields[$field] = new ValidatorField($rules);
    }
  }

  /**
   * Get a field or create it if it doesn't exist
   * @param string $field Field name
   * @return ValidatorField A validator field
   */
  public function __get($field) {
    return $this->get($field);
  }

  /**
   * Check whether or not a field is set
   * @param string $field Field name
   * @return bool True if set, false otherwise
   */
  public function __isset($field) {
    return isset($this->fields[$field]);
  }

  /**
   * Remove a validator field
   * @param string $field Field name
   */
  public function __unset($field) {
    $this->remove($field);
  }

  /**
   * Get a field or create it if it doesn't exist
   * @param string $field Field name
   * @return ValidatorField A validator field
   */
  public function get($field) {
    if (!isset($this->fields[$field])) {
      $this->fields[$field] = new ValidatorField();
    }
    return $this->fields[$field];
  }

  /**
   * Remove a validator field
   * @param string $field Field name
   */
  public function remove($field) {
    if (isset($this->fields[$field])) {
      unset($this->fields[$field]);
    }
  }

  /**
   * Get array of all fields
   * @return array Associative array of field names and {@see ValidatorField}
   * objects
   */
  public function getFields() {
    return $this->fields;
  }
}

