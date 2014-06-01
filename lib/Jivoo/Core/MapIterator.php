<?php
class MapIterator implements Iterator {

  private $map;
  
  public function __construct($map) {
    $this->map = $map;
  }
  
  public function current() {
    return current($this->map);
  }
  
  public function next() {
    next($this->map);
  }
  
  public function key() {
    return key($this->map);
  }
  
  public function valid() {
    return key($this->map) !== null;
  }
  
  public function rewind() {
    reset($this->map);
  }
}