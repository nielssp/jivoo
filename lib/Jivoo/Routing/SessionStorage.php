<?php
/**
 * Session storage access
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set session values.
 * @property-read string $id Session id
 * @property-read FlashMap $flash Flash messages
 * @package Jivoo\Routing
 */
class SessionStorage implements arrayaccess {
  /**
   * @var string Session prefix
   */
  private $prefix = '';
  
  /**
   * @var array Associative array of uid and Flash-objects
   */
  private $messages = array();
  
  private $flash;
  
  /**
   * Constructor
   * @param string $prefix Session prefix to use
   * @param string $clientIp Client IP for verification
   */
  public function __construct($prefix = '', $clientIp = null) {
    session_start();
    $this->prefix = $prefix;
    $this->flash = new FlashMap($this);
  }
  
  public function __destruct() {
    if ($this->id != '')
      $this->flash->save();
  }
  
  /**
   * Get value of property
   * @param string $property Name of property
   * @return mixed Value
   */
  public function __get($property) {
    switch ($property) {
      case 'id':
        return session_id();
      case 'flash':
        return $this->flash;
    }
  }
  
  /**
   * Reopen a closed session
   * @return boolean True on success, false on failure
   */
  public function open() {
    return session_start();
  }
  
  /**
   * Close session and store data. Unlocks session data.
   * Allows other scripts to use session.
   */
  public function close() {
    $this->flash->save();
    session_write_close();
  }
  
  /**
   * Replace session id with new one
   * @return boolean True on success, false on failure
   */
  public function regenerate() {
    return session_regenerate_id();
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
   * Get value, return as reference
   * @param string $name Key
   * @return mixed Value
   */
  private function &get($name) {
    return $_SESSION[$this->prefix . $name];
  }

  /**
   * Get a value
   * @param string $name Key
   * @return mixed Value
   */
  public function offsetGet($name) {
    return $this->get($name);
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
