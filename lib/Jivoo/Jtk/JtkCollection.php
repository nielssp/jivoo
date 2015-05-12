<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Core\Lib;
/**
 * An ordered collection of other JTK objects.
 */
class JtkCollection extends JtkObject implements \Countable, \IteratorAggregate, \ArrayAccess {
  /**
   * @var JtkObject[] Contents.
   */
  private $items = array();
  
  /**
   * @var string Name of object type.
   */
  private $type;
  
  /**
   * Construct collection.
   * @param string $type Parent class of objects in collection.
   */
  public function __construct($type = 'Jivoo\Jtk\JtkObject') {
    $this->type = $type;
  }

  /**
   * Append one or more objects.
   * @param JtkObject|JtkObject[] $item Object or array of objects with
   * optional keys.
   * @param string $id Optional id for object.
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
      Lib::assumeSubclassOf($item, $this->type);
      if (isset($id))
        $this->items[$id] = $item;
      else
        $this->items[] = $item;
    }
  }

  /**
   * Prepend one or more objects.
   * @param JtkObject|JtkObject[] $item Object or array of objects with
   * optional keys.
   * @param string $id Optional id for object.
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
      Lib::assumeSubclassOf($item, $this->type);
      if (isset($id))
        $itemArray = array($id => $item);
      else
        $itemArray = array($item);
      $this->items = array_merge($itemArray, $this->items);
    }
  }

  /**
   * Insert one or more objects.
   * @param int $offset The offset to insert the objects(s) at.
   * @param JtkObject|JtkObject[] $item Object or array of objects with
   * optional keys.
   * @param string $id Optional id for object.
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
      Lib::assumeSubclassOf($item, $this->type);
      if (isset($id))
        $itemArray = array($id => $item);
      else
        $itemArray = array($item);
      $head = array_splice($this->items, 0, $offset);
      $this->items = array_merge($head, $itemArray, $this->items);
    }
  }
  
  /**
   * Create new object (if $type is set and is constructable) and append it.
   * @param mixed $args,... Constructor parameters for class. 
   * @return JtkObject The object.
   */
  public function appendNew() {
    $args = func_get_args();
    $ref  = new ReflectionClass($this->type);
    $object = $ref->newInstanceArgs($args);
    $this->append($object);
    return $object;
  }

  /**
   * Create new object (if $type is set and is constructable) and prepend it.
   * @param mixed $args,... Constructor parameters for class.
   * @return JtkObject The object.
   */
  public function prependNew() {
    $args = func_get_args();
    $ref  = new ReflectionClass($this->type);
    $object = $ref->newInstanceArgs($args);
    $this->prepend($object);
    return $object;
  }

  /**
   * Create new object (if $type is set and is constructable) and insert it.
   * @param int $offset The offset to insert the objects at.
   * @param mixed $args,... Constructor parameters for class.
   * @return JtkObject The object.
   */
  public function insertNew($offset) {
    $args = func_get_args();
    $ref  = new ReflectionClass($this->type);
    $object = $ref->newInstanceArgs(array_slice($args, 1));
    $this->insert($offset, $object);
    return $object;
  }

  /**
   * Create new named object (if $type is set and is constructable) and append it.
   * @param string $id Id for object.
   * @param mixed $args,... Constructor parameters for class.
   * @return JtkObject The object.
   */
  public function appendNewId($id) {
    $args = func_get_args();
    $ref  = new ReflectionClass($this->type);
    $object = $ref->newInstanceArgs($args);
    $this->append($object, $id);
    return $object;
  }
  
  /**
   * Create new named object (if $type is set and is constructable) and prepend it.
   * @param string $id Id for object.
   * @param mixed $args,... Constructor parameters for class.
   * @return JtkObject The object.
   */
  public function prependNewId($id) {
    $args = func_get_args();
    $ref  = new ReflectionClass($this->type);
    $object = $ref->newInstanceArgs($args);
    $this->prepend($object, $id);
    return $object;
  }
  
  /**
   * Create new named object (if $type is set and is constructable) and insert it.
   * @param int $offset The offset to insert the objects at.
   * @param string $id Id for object.
   * @param mixed $args,... Constructor parameters for class.
   * @return JtkObject The object.
   */
  public function insertNewId($offset, $id) {
    $args = func_get_args();
    $ref  = new ReflectionClass($this->type);
    $object = $ref->newInstanceArgs(array_slice($args, 1));
    $this->insert($offset, $object, $id);
    return $object;
  }
  
  /**
   * Remove the object with the specified id. 
   * @param string $id Object id.
   */
  public function remove($id) {
    if (isset($this->items[$id]))
      unset($this->items[$id]);
  }
  
  /**
   * Remove object at the specified offset.
   * @param int $offset Offset.
   */
  public function removeOffset($offset) {
    array_splice($this->items, $offset, 1);
  }
  
  /**
   * Get the object at the specified offset.
   * @param int $offset Offset.
   * @return JtkObject Object.
   */
  public function objectAt($offset) {
    $slice = array_slice($this->items, $offset, 1);
    return $slice[0];
  }
  
  /**
   * Get offset of the object the specified id.
   * @param string $id Object id.
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
   * Get object with specified id.
   * @param string $id object id.
   * @return JtkObject Object or null if undefined.
   */
  public function offsetGet($id) {
    if (isset($this->items[$id]))
      return $this->items[$id];
    return null;
  }

  /**
   * Append or replace an object.
   * @param string|null $id Optional object id.
   * @param JtkObject Object.
   */
  public function offsetSet($id, $item) {
    if (isset($id) and isset($this->items[$id]))
      $this->items[$id] = $item;
    else
      $this->append($item, $id);
  }
  
  /**
   * Remove object with specified id.
   * @param string Object id.
   */
  public function offsetUnset($id) {
    $this->remove($id);
  }
  
  /**
   * Whether or not an object with the specified id exists.
   * @param string Item id.
   * @return bool True if object exists.
   */
  public function offsetExists($id) {
    return isset($this->items[$id]);
  }
  
  /**
   * Get iterator for objects.
   * @return ArrayIterator Iterator. 
   */
  public function getIterator() {
    return new \ArrayIterator($this->items);
  }
  
  /**
   * Get number of objects in collection.
   * @return int Number of objects.
   */
  public function count() {
    return count($this->items);
  }
}