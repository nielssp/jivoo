<?php

class Cookies implements arrayaccess {
  
  private $prefix = '';
  
  private $cookies = array();
  
  public function __construct($cookies = array(), $prefix = '') {
    $this->cookies = $cookies;
    $this->prefix = $prefix;
  }

  public function setCookie($name, $value, $expire = NULL, $path = WEBPATH) {
    if (!isset($expire)) {
      $expire = time() + 60 * 60 * 24 * 365;
    }
    setcookie($this->prefix . $name, $value, $expire, $path);
  }

  public function offsetExists($name) {
    return isset($this->cookies[$this->prefix . $name]);
  }
  
  public function offsetGet($name) {
    return $this->cookies[$this->prefix . $name];
  }
  
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $this->setCookie($name, $value);
      $this->cookies[$name] = $value;
    }
  }
  
  public function offsetUnset($name) {
    $this->setCookie($name, '', time());
    unset($this->cookies[$this->prefix . $name]);
  }
  
}