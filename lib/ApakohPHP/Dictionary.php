<?php
/**
 * A collection of key/value pairs
 * @package PeanutCMS
 */
class Dictionary {
  private $array = array();
  private $readOnly = false;

  /**
   * Get the value associated with a key
   * @param string $key Key
   * @throws DictionaryKeyInvalidException If key does note exist
   * @return mixed Value associated with key
   */
  public function __get($key) {
    if (isset($this->array[$key])) {
      return $this->array[$key];
    }
    else {
      throw new DictionaryKeyInvalidException(tr('Invalid dictionary key: %1', $key));
    }
  }

  /**
   * Associate a value with a key
   * @param string $key Key
   * @param mixed $value Value
   * @throws DictionaryReadOnlyException if Dictionary is read-only
   */
  public function __set($key, $value) {
    if ($this->readOnly) {
      throw new DictionaryReadOnlyException(tr('Dictionary is read-only.'));
    }
    $this->array[$key] = $value;
  }

  /**
   * Whether or not a key exists
   * @param string $key Key
   * @return bool True if key exists, false otherwise
   */
  public function __isset($key) {
    return isset($this->array[$key]);
  }

  /**
   * Delete a key from the dictionary
   * @param string $key Key
   * @throws DictionaryReadOnlyException if Dictionary is read-only
   */
  public function __unset($key) {
    if ($this->readOnly) {
      throw new DictionaryReadOnlyException(tr('Dictionary is read-only.'));
    }
    unset($this->array[$key]);
  }

  /**
   * Whether or not the dictionary is read-only
   * @return bool True if read-only, false if not
   */
  public function isReadOnly() {
    return $this->readOnly;
  }

  /**
   * Construct a dictionary
   * @param array $array Key/value pairs to build dictionary from
   * @param bool $readOnly Whether or not this dictionary should be read-only
   */
  public function __construct($array = array(), $readOnly = false) {
    $this->array = $array;
    $this->readOnly = $readOnly;
  }
}

/**
 * Exception thrown when a key does not exist in a dictionary
 * @package PeanutCMS
 */
class DictionaryKeyInvalidException extends Exception {}

/**
 * Exception thrown when writing to a read-only dictionary
 * @package PeanutCMS
 */
class DictionaryReadOnlyException extends Exception {}
