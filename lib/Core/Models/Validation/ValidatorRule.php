<?php
/**
 * A custom validator rule
 * @package Core\Models
 * @property string $message The error message for the rule
 */
class ValidatorRule {
  /**
   * @var string Error message for rule
   */
  private $message = 'Invalid value.';
  
  /**
   * @var array Associative array of rule names and values
   */
  private $rules = array();
  
  /**
   * Constructor.
   * @param array $rules Associative array of validation rules for field
   */
  public function __construct($rules = array()) {
    $this->rules = $rules;
    if (isset($this->rules['message'])) {
      $this->message = $this->rules['message'];
      unset($this->rules['message']);
    }
  }

  /**
   * Get a single subrule
   * @param string $rule Rule name
   * @return mixed|ValidatorRule Rule value or a more complex rule
   */
  public function __get($rule) {
    if ($rule == 'message') {
      return $this->getMessage();
    }
    return $this->get($rule);
  }

  /**
   * Add/set a subrule
   * @param string $rule Rule name
   * @param mixed $value Rule value
   */
  public function __set($rule, $value) {
    if ($rule == 'message') {
      $this->setMessage($value);
    }
    $this->add($rule, $value);
  }

  /**
   * Check whether or not a subrule exists
   * @param string $rule Rule name
   * @return bool True if it exists, false otherwise
   */
  public function __isset($rule) {
    return isset($this->rules[$rule]);
  }

  /**
   * Remove a subrule
   * @param string $rule Rule name
   */
  public function __unset($rule) {
    $this->remove($rule);
  }

  /**
   * Set error message for rule
   * @param string $message Error message
   */
  public function setMessage($message) {
    $this->message = $message;
  }

  /**
   * Get error message for rule
   * @return string Error message
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Get a single subrule
   * @param string $rule Rule name
   * @return mixed|ValidatorRule Rule value or a more complex rule
   */
  public function get($rule) {
    if (!isset($this->rules[$rule])) {
      return null;
    }
    return $this->rules[$rule];
  }

  /**
   * Add/set a subrule
   * @param string $rule Rule name
   * @param mixed $value Rule value
   */
  public function add($rule, $value = true) {
    $this->rules[$rule] = $value;
    return $this;
  }

  /**
   * Remove a subrule
   * @param string $rule Rule name
   */
  public function remove($rule) {
    if (isset($this->rules[$rule])) {
      unset($this->rules[$rule]);
    }
  }

  /**
   * Get all subrules
   * @return array Associative array of rule names and values
   */
  public function getRules() {
    return $this->rules;
  }

  public function validate(IRecord $record, $field) {
    foreach ($this->rules as $name => $rule) {
      $result = Validator::validateRule($record, $field, $name, $rule);
      if ($result !== true)
        return $this->message;
    }
    return true;
  }
}
