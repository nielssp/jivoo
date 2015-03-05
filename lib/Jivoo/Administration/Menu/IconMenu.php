<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Administration\Menu;

class IconMenu extends IconMenuItem implements \ArrayAccess, \Iterator {
  
  private $items = array();
  
  public function __get($property) {
    switch ($property) {
      case 'items':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function fromArray($array) {
    foreach ($array as $key => $value) {
      $this->items[$key] = $value;
    }
  }
  
  public function prepend(IconMenuItem $item) {
    array_unshift($this->items, $item);
  }
  
  public function append(IconMenuItem $item) {
    $this->items[] = $item;
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

  public function rewind() {
    reset($this->items);
  }
  
  public function current() {
    return current($this->items);
  }
  
  public function key() {
    return key($this->items);
  }
  
  public function next() {
    next($this->items);
  }
  
  public function valid() {
    return key($this->items) !== null;
  }
  
  public static function menu($label, $route, $icon = null, $items = array()) {
    $menu = new IconMenu($label, $route, $icon);
    $menu->items = $items;
    return $menu;
  }
  
  public static function item($label, $route, $icon = null, $badge = null) {
    return new IconMenuItem($label, $route, $icon, $badge);
  }
}