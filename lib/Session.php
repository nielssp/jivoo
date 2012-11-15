<?php
/**
 * Session storage access
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set session values.
 * @package PeanutCMS
 */
class Session implements arrayaccess {
  
  private $prefix = '';
  
  /**
   * Constructor
   * @param string $prefix Session prefix to use
   */
  public function __construct($prefix = '') {
    $this->prefix = $prefix;
  }

  /**
   * Whether or not a key exists.
   * @param string $name Key
   * @return bool True if it does, false otherwise
   */
  public function offsetExists($name) {
    return isset($_SESSION[$this->prefix . $name]);
  }
  
  /**
   * Get a value
   * @param string $name Key
   * @return mixed Value
   */
  public function offsetGet($name) {
    return $_SESSION[$this->prefix . $name];
  }

  /**
   * Associate a value with a key
   * @param string $name Key
   * @param mixed $value Value
   */
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $_SESSION[$this->prefix . $name] = $value;
    }
  }
  
  /**
   * Delete a key
   * @param string $name Key
   */
  public function offsetUnset($name) {
    unset($_SESSION[$this->prefix . $name]);
  }
  
}
