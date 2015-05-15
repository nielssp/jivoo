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
  /**
   * @var MenuItem[] Contents of menu.
   */
  private $items = array();

  /**
   * Construct menu.
   * @param string $label Label, i.e. title of menu.
   * @param string $icon Optional icon path or name, see
   * {@see Jivoo\Jtk\IconHelper}.
   */
  public function __construct($label, $icon = null) {
    parent::__construct($label);
    $this->icon = $icon;
    $this->route = 'null:';
  }

  /**
   * {@inheritdoc}
   */
  public function isMenu() {
    return true;
  }
  
  /**
   * Append one or more menu items.
   * @param MenuItem|MenuItem[] $item Menu item or array of menu items with
   * optional keys.
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
   * @param MenuItem|MenuItem[] $item Menu item or array of menu items with
   * optional keys. The order of the items in the array will be maintained. 
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
   * @param MenuItem|MenuItem[] $item Menu item or array of menu items with
   * optional keys. The order of the items in the array will be maintained.
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
  
  /**
   * Remove the item with the specified id. 
   * @param string $id Item id.
   */
  public function remove($id) {
    if (isset($this->items[$id]))
      unset($this->items[$id]);
  }
  
  /**
   * Remove item at the specified offset.
   * @param int $offset Offset.
   */
  public function removeOffset($offset) {
    array_splice($this->items, $offset, 1);
  }
  
  /**
   * Get the item at the specified offset.
   * @param int $offset Offset.
   * @return MenuItem Item.
   */
  public function itemAt($offset) {
    $slice = array_slice($this->items, $offset, 1);
    return $slice[0];
  }
  
  /**
   * Get offset of the item with the specified id.
   * @param string $id Item id.
   * @return int|null The offset or null if id is undefined.
   */
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
  
  /**
   * Create a menu and append it.
   * @param string $label Title of menu.
   * @param string $id Optional item id.
   * @return Menu Menu.
   */
  public function appendMenu($label, $id = null) {
    $menu = new Menu($label);
    $this->append($menu, $id);
    return $menu;
  }

  /**
   * Create a menu and prepend it.
   * @param string $label Title of menu.
   * @param string $id Optional item id.
   * @return Menu Menu.
   */
  public function prependMenu($label, $id = null) {
    $menu = new Menu($label);
    $this->prepend($menu, $id);
    return $menu;
  }

  /**
   * Create a menu and insert it.
   * @param int $offset Offset.
   * @param string $label Title of menu.
   * @param string $id Optional item id.
   * @return Menu Menu.
   */
  public function insertMenu($offset, $label, $id = null) {
    $menu = new Menu($label);
    $this->insert($offset, $menu, $id);
    return $menu;
  }

  /**
   * Create a separator and append it.
   * @param string $id Optional item id.
   * @return MenuSeparator Separator.
   */
  public function appendSeparator($id = null) {
    $sep = new MenuSeparator();
    $this->append($sep, $id);
    return $sep;
  }

  /**
   * Create a separator and prepend it.
   * @param string $id Optional item id.
   * @return MenuSeparator Separator.
   */
  public function prependSeparator($id = null) {
    $sep = new MenuSeparator();
    $this->prepend($sep, $id);
    return $sep;
  }

  /**
   * Create a separator and insert it.
   * @param int $offset Offset.
   * @param string $id Optional item id.
   * @return MenuSeparator Separator.
   */
  public function insertSeparator($offset, $id = null) {
    $sep = new MenuSeparator();
    $this->insert($offset, $sep, $id);
    return $sep;
  }

  /**
   * Create an action and append it.
   * @param string $label Label of action.
   * @param string $id Optional item id.
   * @return MenuAction Action.
   */
  public function appendAction($label, $id = null) {
    $action = new MenuAction($label);
    $this->append($action, $id);
    return $action;
  }

  /**
   * Create an action and prepend it.
   * @param string $label Label of action.
   * @param string $id Optional item id.
   * @return MenuAction Action.
   */
  public function prependAction($label, $id = null) {
    $action = new MenuAction($label);
    $this->prepend($action, $id);
    return $action;
  }

  /**
   * Create an action and insert it.
   * @param int $offset Offset.
   * @param string $label Label of action.
   * @param string $id Optional item id.
   * @return MenuAction Action.
   */
  public function insertAction($offset, $label, $id = null) {
    $action = new MenuAction($label);
    $this->insert($offset, $action, $id);
    return $action;
  }
  
  /**
   * Get item with specified id.
   * @param string $id Item id.
   * @return MenuItem Item or null if undefined.
   */
  public function offsetGet($id) {
    if (isset($this->items[$id]))
      return $this->items[$id];
    return null;
  }

  /**
   * Append or replace a menu item.
   * @param string|null $id Optional item id.
   * @param MenuItem Item.
   */
  public function offsetSet($id, $item) {
    if (isset($id) and isset($this->items[$id]))
      $this->items[$id] = $item;
    else
      $this->append($item, $id);
  }
  
  /**
   * Remove item with specified id.
   * @param string Item id.
   */
  public function offsetUnset($id) {
    $this->remove($id);
  }
  
  /**
   * Whether or not an item with the specified id exists.
   * @param string Item id.
   * @return bool True if item exists.
   */
  public function offsetExists($id) {
    return isset($this->items[$id]);
  }
  
  /**
   * Get iterator for menu items.
   * @return ArrayIterator Iterator. 
   */
  public function getIterator() {
    return new \ArrayIterator($this->items);
  }
  
  /**
   * Get number of menu items.
   * @return int Number of items.
   */
  public function count() {
    return count($this->items);
  }
}