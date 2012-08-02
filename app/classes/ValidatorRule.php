<?php

class ValidatorRule {
  private $message = 'Invalid value.';
  private $rules = array();

  public function __construct($rules = array()) {
    $this->rules = $rules;
    if (isset($this->rules['message'])) {
      $this->message = $this->rules['message'];
      unset($this->rules['message']);
    }
  }

  public function __get($rule) {
    if ($rule == 'message') {
      return $this->getMessage();
    }
    return $this->get($rule);
  }

  public function __set($rule, $value) {
    if ($rule == 'message') {
      $this->setMessage($value);
    }
    $this->add($rule, $value);
  }

  public function __isset($rule) {
    return isset($this->rules[$rule]);
  }

  public function __unset($rule) {
    $this->remove($rule);
  }

  public function setMessage($message) {
    $this->message = $message;
  }

  public function getMessage() {
    return $this->message;
  }

  public function get($rule) {
    if (!isset($this->rules[$rule])) {
      return NULL;
    }
    return $this->rules[$rule];
  }

  public function add($rule, $value = TRUE) {
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