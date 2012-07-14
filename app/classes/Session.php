<?php

class Session implements arrayaccess {
  
  private $prefix = '';
  
  public function __construct($prefix = '') {
    $this->prefix = $prefix;
  }

  public function offsetExists($name) {
    return isset($_SESSION[$this->prefix . $name]);
  }
  
  public function offsetGet($name) {
    return $_SESSION[$this->prefix . $name];
  }
  
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $_SESSION[$this->prefix . $name] = $value;
    }
  }
  
  public function offsetUnset($name) {
    unset($_SESSION[$this->prefix . $name]);
  }
  
}
