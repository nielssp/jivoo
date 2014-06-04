<?php
class IconMenu extends IconMenuItem implements ArrayAccess, Iterator {
  
  private $items = array();
  
  public function __get($property) {
    switch ($property) {
      case 'items':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function offsetExists($key) {
    return isset($this->items[$key]);    
  }
  public function offsetGet($key) {
    return $this->items[$key];    
  }
  public function offsetSet($key, $value) {
    if (!isset($key))
      $this->items[] = $value;
    else
      $this->items[$key] = $value;
  }

  public function offsetUnset($key) {
    unset($this->items[$key]);
  }

  function rewind() {
    reset($this->items);
  }
  
  function current() {
    return current($this->items);
  }
  
  function key() {
    return key($this->items);
  }
  
  function next() {
    next($this->items);
  }
  
  function valid() {
    return key($this->items) !== null;
  }
}