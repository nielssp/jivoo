<?php
/**
 * Session storage access
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set session values.
 * @package Core
 * @subpackage Routing
 */
class Session implements arrayaccess {

  private $prefix = '';
  private $messages = array();
  
  
  /**
   * Constructor
   * @param string $prefix Session prefix to use
   */
  public function __construct($prefix = '') {
    session_start();
    $this->prefix = $prefix;
    if (!isset($this['messages'])) {
      $this['messages'] = array();
    }
    foreach ($this['messages'] as $uid => $flash) {
      $this->messages[$uid] = Flash::fromArray($this, $flash);
    }
  }
  
  public function __get($property) {
    switch ($property) {
      case 'messages':
        return $this->messages;
      case 'alerts':
        $result = array();
        foreach ($this->messages as $flash) {
          if ($flash->type == 'alert') {
            $result[] = $flash;
          }
        }
        return $result;
      case 'notices':
        $result = array();
        foreach ($this->messages as $flash) {
          if ($flash->type == 'notice') {
            $result[] = $flash;
          }
        }
        return $result;
    }
  }
  
  public function notice($message, $label = null) {
    $flash = new Flash($this, $message, 'notice', $label);
    $this->messages[$flash->uid] = $flash;
    $messages = $this['messages'];
    $messages[$flash->uid] = $flash->toArray();
    $this['messages'] = $messages;
  }
  
  public function alert($message, $label = null) {
    $flash = new Flash($this, $message, 'alert', $label);
    $this->messages[$flash->uid] = $flash;
    $messages = $this['messages'];
    $messages[$flash->uid] = $flash->toArray();
    $this['messages'] = $messages;
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
