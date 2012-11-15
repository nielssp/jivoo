<?php
class BackendCategory implements arrayaccess, IGroupable, Iterator {
  private $items = array();
  private $keys = array();
  private $label = '';
  private $group = 0;
  private $position = 0;
  private $backend = null;
  private $auth = null;

  public function __construct(Backend $backend, Authentication $authentication) {
    $this->backend = $backend;
    $this->auth = $authentication;
  }
  
  public function __get($property) {
    switch ($property) {
      case 'label':
      case 'group':
        return $this->$property;
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'label':
      case 'group':
        $this->$property = $value;
    }
  }
  
  public function setup($label, $group = null) {
    $this->label = $label;
    if (isset($group)) {
      $this->group = $group;
    }
    return $this;
  }
  
  public function group() {
    groupObjects($this->items);
  }

  public function getGroup() {
    return $this->group;
  }
  
  public function count() {
    $count = 0;
    foreach ($this->items as $key => $item) {
      if (!$item->hasAccess()) {
        unset($this->items[$key]);
      }
      else {
        $count++;
      }
    }
    return $count;
  }

  public function offsetExists($item) {
    return isset($this->items[$item]);
  }

  public function offsetGet($item) {
    if (!isset($this->items[$item])) {
      $this->items[$item] = new BackendItem($this->backend, $this->auth);
      $this->items[$item]->label = $item;
    }
    return $this->items[$item];
  }

  public function offsetSet($item, $value) {
    if (is_null($item)) {
    }
    else {
      if ($value instanceof BackendItem) {
        $this->items[$item] = $value;
      }
    }
  }

  public function offsetUnset($item) {
    unset($this->items[$item]);
  }
  
  function rewind() {
    $this->position = 0;
    $this->keys = array_keys($this->items);
  }
  
  function current() {
    return $this->items[$this->keys[$this->position]];
  }
  
  function key() {
    return $this->keys[$this->position];
  }
  
  function next() {
    ++$this->position;
  }
  
  function valid() {
    return isset($this->keys[$this->position]);
  }
}