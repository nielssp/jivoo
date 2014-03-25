<?php
/**
 * A collection of key/value pairs
 * @package Core
 */
interface IDictionary {
  
  /**
   * Whether or not the dictionary is read-only
   * @return bool True if read-only, false if not
   */
  public function isReadOnly();
  
  /**
   * Get the value associated with a key
   * @param string $key Key
   * @throws DictionaryKeyInvalidException If key does note exist
   * @return mixed Value associated with key
   */
  public function __get($key);
  
  /**
   * Associate a value with a key
   * @param string $key Key
   * @param mixed $value Value
   * @throws DictionaryReadOnlyException if Dictionary is read-only
   */
  public function __set($key, $value);
  
  /**
   * Whether or not a key exists
   * @param string $key Key
   * @return bool True if key exists, false otherwise
   */
  public function __isset($key);
  
  /**
   * Delete a key from the dictionary
   * @param string $key Key
   * @throws DictionaryReadOnlyException if Dictionary is read-only
   */
  public function __unset($key);
}

/**
 * Exception thrown when a key does not exist in a dictionary
 * @package Core
 */
class DictionaryKeyInvalidException extends Exception {}

/**
 * Exception thrown when writing to a read-only dictionary
 * @package Core
 */
class DictionaryReadOnlyException extends Exception {}