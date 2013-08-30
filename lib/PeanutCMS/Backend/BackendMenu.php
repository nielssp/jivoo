<?php
/**
 * Backend menu
 * @package PeanutCMS\Backend
 */
class BackendMenu implements IGroupable, Iterator {
  private $items = array();
  
  private $position = 0;
  
  private $label = '';
  
  private $group = 0;
  
  public function __constructor($label = '', $group = 0) {
    $this->label = $label;
    $this->group = $group;
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
  
  public function add(BackendMenuItem $item) {
    $this->items[] = $item;
    return $this;
  }
  
  public function item($label, $route = null, $group = 0, $permission = 'backend.access') {
    $this->items[] = new BackendMenuItem($label, $route, $group, $permission);
    return $this;
  }
  
  public function setup($label, $group = null) {
    $this->label = $label;
    if (isset($group)) {
      $this->group = $group;
    }
    return $this;
  }

  public function group() {
    Utilities::groupObjects($this->items);
  }

  public function getGroup() {
    return $this->group;
  }
  
  public function prepare(Authentication $auth) {
    $hasItems = false;
    foreach ($this->items as $key => $item) {
      if (!$auth->hasPermission($item->permission)) {
        unset($this->items[$key]);
      }
      else {
        $hasItems = true;
      }
    }
    if ($hasItems) {
      $this->group();
    }
    return $hasItems;
  }

  public function rewind() {
    $this->position = 0;
  }
  
  public function current() {
    return $this->items[$this->position];
  }
  
  public function key() {
    return $this->position;
  }
  
  public function next() {
    ++$this->position;
  }
  
  public function valid() {
    return isset($this->items[$this->position]);
  }
}