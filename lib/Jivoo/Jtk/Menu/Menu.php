<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk\Menu;

/**
 * A menu.
 * @property string $icon Icon path or name.
 */
class Menu extends LabelMenuItem implements \Countable, \IteratorAggregate, \ArrayAccess {
  
  private $items = array();

  public function __construct($label, $icon = null) {
    parent::__construct($label);
    $this->icon = $icon;
  }
  /**
   * Append one or more menu items.
   * @param MenuItem|MenuItem[] $item Menu item or array of menu items.
   * @param string $id Optional id for item.
   */
  public function append($item, $id = null) {
    if (is_array($item)) {
      foreach ($item as $id => $it) {
        if (!is_string($id))
          $id = null;
        $this->append($it, $id);
      }
    }
    else {
      if (isset($id))
        $this->items[$id] = $item;
      else
        $this->items[] = $item;
    }
  }

  /**
   * Prepend one or more menu items.
   * @param MenuItem|MenuItem[] $item Menu item or array of menu items.
   * @param string $id Optional id for item.
   */
  public function prepend($item, $id = null) {
    if (is_array($item)) {
      $item = array_reverse($item, true);
      foreach ($item as $id => $it) {
        if (!is_string($id))
          $id = null;
        $this->prepend($it, $id);
      }
    }
    else {
      if (isset($id))
        $itemArray = array($id => $item);
      else
        $itemArray = array($item);
      $this->items = array_merge($itemArray, $this->items);
    }
  }

  /**
   * Insert one or more menu items.
   * @param int $offset The offset to insert the item(s) at.
   * @param MenuItem|MenuItem[] $item Menu item or array of menu items.
   * @param string $id Optional id for item.
   */
  public function insert($offset, $item, $id = null) {
    if (is_array($item)) {
      $item = array_reverse($item, true);
      foreach ($item as $id => $it) {
        if (!is_string($id))
          $id = null;
        $this->insert($offset, $it, $id);
      }
    }
    else {
      if (isset($id))
        $itemArray = array($id => $item);
      else
        $itemArray = array($item);
      $head = array_splice($this->items, 0, $offset);
      $this->items = array_merge($head, $itemArray, $this->items);
    }
  }
  
  public function remove($id) {
    if (isset($this->items[$id]))
      unset($this->items[$id]);
  }
  
  public function removeOffset($offset) {
    array_splice($this->items, $offset, 1);
  }
  
  public function itemAt($offset) {
    $slice = array_slice($this->items, $offset, 1);
    return $slice[0];
  }
  
  public function getOffset($id) {
    if (!isset($this->items[$id]))
      return null;
    $keys = array_keys($this->items);
    $n = count($keys);
    $result = null;
    foreach ($keys as $offset => $key) {
      if ($key === $id) {
        $result = $offset;
        break;
      }
    }
    return $result;
  }
  
  public function appendMenu($label, $id = null) {
    $menu = new Menu($label);
    $this->append($menu, $id);
    return $menu;
  }

  public function prependMenu($label, $id = null) {
    $menu = new Menu($label);
    $this->prepend($menu, $id);
    return $menu;
  }

  public function insertMenu($offset, $label, $id = null) {
    $menu = new Menu($label);
    $this->insert($offset, $menu, $id);
    return $menu;
  }
  
  public function appendSeparator($id = null) {
    $sep = new MenuSeparator();
    $this->append($sep, $id);
    return $sep;
  }
  
  public function prependSeparator($id = null) {
    $sep = new MenuSeparator();
    $this->prepend($sep, $id);
    return $sep;
  }
  
  public function insertSeparator($offset, $id = null) {
    $sep = new MenuSeparator();
    $this->insert($offset, $sep, $id);
    return $sep;
  }
  
  public function appendAction($label, $route = null, $icon = null, $id = null) {
    $action = new MenuAction($label, $route, $icon);
    $this->append($action, $id);
    return $action;
  }
  
  public function prependAction($label, $route = null, $icon = null, $id = null) {
    $action = new MenuAction($label, $route, $icon);
    $this->prepend($action, $id);
    return $action;
  }
  
  public function insertAction($offset, $label, $route = null, $icon = null, $id = null) {
    $action = new MenuAction($label, $route, $icon);
    $this->insert($offset, $action, $id);
    return $action;
  }
  
  public function offsetGet($id) {
    if (isset($this->items[$id]))
      return $this->items[$id];
    return null;
  }
  
  public function offsetSet($id, $item) {
    if (isset($id) and isset($this->items[$id]))
      $this->items[$id] = $item;
    else
      $this->append($item, $id);
  }
  
  public function offsetUnset($id) {
    $this->remove($id);
  }
  
  public function offsetExists($id) {
    return isset($this->items[$id]);
  }
  
  public function getIterator() {
    return new \ArrayIterator($this->items);
  }
  
  public function count() {
    return count($this->items);
  }
}