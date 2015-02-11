<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * A collection of key/value pairs.
 * @package Jivoo\Core
 */
class Map implements \IteratorAggregate, \ArrayAccess, \Countable {
  /**
   * @var array Key/value pairs.
   */
  private $map = array();
  
  /**
   * @var bool Whether or not dictionary is read-only.
   */
  private $readOnly = false;

  /**
   * Get value of key.
   * @param string $key Key.
   * @throws MapKeyInvalidException If key not defined.
   * @return mixed Value of key.
   */
  public function __get($key) {
    if (!isset($this->map[$key]))
      throw new MapKeyInvalidException(tr('Invalid map key: %1', $key));
    return $this->map[$key];
  }

  /**
   * Set value of key.
   * @param string $key Key.
   * @param mixed $value Value.
   * @throws MapReadOnlyException If map is read-only.
   */
  public function __set($key, $value) {
    if ($this->readOnly)
      throw new MapReadOnlyException(tr('Map is read-only.'));
    $this->map[$key] = $value;
  }

  /**
   * Check whether a key is set.
   * @param string $key Key.
   * @return bool True if set, false otherwise.
   */
  public function __isset($key) {
    return isset($this->map[$key]);
  }

  /**
   * Unset a key.
   * @param string $key Key.
   * @throws MapReadOnlyException If map is read-only.
   */
  public function __unset($key) {
    if ($this->readOnly)
      throw new MapReadOnlyException(tr('Map is read-only.'));
    unset($this->map[$key]);
  }

  /**
   * Whether or not map is read-only.
   * @return boolean True if read-only, false otherwise.
   */
  public function isReadOnly() {
    return $this->readOnly;
  }
  
  /**
   * Get value of key.
   * @param string $key Key.
   * @throws MapKeyInvalidException If key not defined.
   * @return mixed Value of key.
   */
  public function offsetGet($key) {
    return $this->__get($key);
  }

  /**
   * Check whether a key is set.
   * @param string $key Key.
   * @return bool True if set, false otherwise.
   */
  public function offsetExists($key) {
    return $this->__isset($key);
  }
  
  /**
   * Set value of key.
   * @param string $key Key.
   * @param mixed $value Value.
   * @throws MapReadOnlyException If map is read-only.
   */
  public function offsetSet($key, $value) {
    if (isset($key))
      $this->__set($key, $value);
  }

  /**
   * Unset a key.
   * @param string $key Key.
   * @throws MapReadOnlyException If map is read-only.
   */
  public function offsetUnset($key) {
    $this->__unset($key);
  }
  
  /**
   * Size of map.
   * @return int Number of key-value pairs in map.
   */
  public function count() {
    return count($this->map);
  }
  
  /**
   * {@inheritdoc}
   */
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

/**
 * Thrown if a key is not defined in map.
 */
class MapKeyInvalidException extends \Exception { }

/**
 * Thrown when editting a read-only map.
 */
class MapReadOnlyException extends \Exception { }
