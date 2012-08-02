<?php

class ValidatorField {
  private $rules = array();

  public function __construct($rules = array()) {
    foreach ($rules as $rule => $value) {
      if (substr($rule, 0, 4) == 'rule') {
        $this->rules[$rule] = new ValidatorRule($value);
      }
      else {
        $this->rules[$rule] = $value;
      }
    }
  }

  public function __get($rule) {
    return $this->get($rule);
  }

  public function __set($rule, $value) {
    $this->add($rule, $value);
  }

  public function __isset($rule) {
    return isset($this->rules[$rule]);
  }

  public function __unset($rule) {
    $this->remove($rule);
  }

  public function get($rule) {
    if (substr($rule, 0, 4) == 'rule') {
      if (!isset($this->rules[$rule])) {
        $this->rules[$rule] = new ValidatorRule();
      }
    }
    else if (!isset($this->rules[$rule])) {
      return NULL;
    }
    return $this->rules[$rule];
  }

  public function add($rule, $value = TRUE) {
    if (substr($rule, 0, 4) == 'rule') {
      if (!isset($this->rules[$rule])) {
        $this->rules[$rule] = new ValidatorRule();
      }
      return $this->rules[$rule];
    }
    $this->rules[$rule] = $value;
    return $this;
  }

  public function remove($rule) {
    if (isset($this->rules[$rule])) {
      unset($this->rules[$rule]);
    }
  }

  public function getRules() {
    return $this->rules;
  }
}