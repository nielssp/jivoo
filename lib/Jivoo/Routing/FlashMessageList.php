<?php
class FlashMessageList implements ArrayAccess, Countable, Iterator {
  
  private $type;
  private $list;
  
  private $iterable;
  
  public function __construct($type, $list = array()) {
    $this->type = $type;
    $this->list = $list;
  }
  
  public function toArray() {
    return $this->list;
  }

  public function offsetGet($key) {
    return $this->list[$key];
  }
  
  public function offsetExists($key) {
    return isset($this->list[$key]);
  }
  
  public function offsetSet($key, $value) {
    if (!isset($key)) {
      $this->list[] = $value;
    }
    else {
      $this->list[$key] = $value;
    }
  }
  
  public function offsetUnset($key) {
    unset($this->list[$key]);
  }
  
  public function count() {
    return count($this->list);
  }
  
  public function clear() {
    $this->list = array();
  }
  

  public function current() {
    unset($this->list[key($this->iterable)]);
    return new FlashMessage(current($this->iterable), $this->type);
  }
  
  public function next() {
    next($this->iterable);
  }
  
  public function key() {
    return key($this->iterable);
  }
  
  public function valid() {
    return key($this->iterable) !== null;
  }
  
  public function rewind() {
    $this->iterable = $this->list;
    reset($this->iterable);
  }
}