<?php
/**
 * A collection of key/value pairs
 * @package Core
 */
class Map implements IteratorAggregate, ArrayAccess, Countable {
  /**
   * @var array Key/value pairs
   */
  private $map = array();
  
  /**
   * @var bool Whether or not dictionary is read-only
   */
  private $readOnly = false;

  public function __get($key) {
    if (!isset($this->map[$key]))
      throw new MapKeyInvalidException(tr('Invalid map key: %1', $key));
    return $this->map[$key];
  }

  public function __set($key, $value) {
    if ($this->readOnly)
      throw new MapReadOnlyException(tr('Map is read-only.'));
    $this->map[$key] = $value;
  }

  public function __isset($key) {
    return isset($this->map[$key]);
  }

  public function __unset($key) {
    if ($this->readOnly)
      throw new MapReadOnlyException(tr('Map is read-only.'));
    unset($this->map[$key]);
  }

  public function isReadOnly() {
    return $this->readOnly;
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
    return count($this->map);
  }
  
  public function getIterator() {
    return new MapIterator($this->map);
  }

  /**
   * Construct a dictionary
   * @param array $array Key/value pairs to build dictionary from
   * @param bool $readOnly Whether or not this dictionary should be read-only
   */
  public function __construct($array = array(), $readOnly = false) {
    $this->map = $array;
    $this->readOnly = $readOnly;
  }
}

class MapKeyInvalidException extends Exception { }
class MapReadOnlyException extends Exception { }
