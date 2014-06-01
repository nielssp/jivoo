<?php
class FlashMap implements IteratorAggregate, ArrayAccess, Countable {

  private $session;
  
  private $lists = array();
  
  public function __construct(SessionStorage $session) {
    $this->session = $session;
    foreach ($this->session['flash'] as $type => $list) {
      $this->lists[$type] = new FlashMessageList($type, $list);
    }
  }
  
  public function save() {
    $map = array();
    foreach ($this->lists as $type => $list) {
      if (count($list) > 0) {
        $map[$type] = $list->toArray();
      }
    }
    $this->session['flash'] = $map;
  }
  
  public function __get($type) {
    if (!isset($this->lists[$type]))
      $this->lists[$type] = new FlashMessageList($type);
    return $this->lists[$type];
  }
  
  public function __set($type, $value) {
    $this[$type][] = $value;
  }
  
  public function __isset($type) {
    return count($this[$type]) > 0;
  }
  
  public function __unset($key) {
    $this[$type]->clear();
  }
  
  public function offsetGet($key) {
    return $this->__get($key);
  }
  
  public function offsetExists($key) {
    return $this->__isset($key);
  }
  
  public function offsetSet($key, $value) {
    if (isset($key))
      $this->__set($key, $value);
  }
  
  public function offsetUnset($key) {
    $this->__unset($key);
  }
  
  public function count() {
    $count = 0;
    foreach ($this->lists as $list) {
      $count += $list->count();
    }
    return $count;
  }
  
  public function getIterator() {
    return new FlashMapIterator($this, $this->lists);
  }
}