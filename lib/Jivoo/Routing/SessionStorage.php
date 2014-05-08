<?php
/**
 * Session storage access
 *
 * Implements arrayaccess, so the []-operator can be used
 * to get and set session values.
 * @property-read string $id Session id
 * @property-read array $messages Associative array of uid and
 * {@see Flash} objects
 * @property-read Flash[] $alerts Alerts only
 * @property-read Flash[] $notices Notices only
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
  
  
  /**
   * Constructor
   * @param string $prefix Session prefix to use
   * @param string $clientIp Client IP for verification
   */
  public function __construct($prefix = '', $clientIp = null) {
    session_start();
    $this->prefix = $prefix;
//     if (isset($clientIp)) {
//       if (isset($this['ip'])) {
//         if ($clientIp != $this['ip']) {
//           // Verification failed, reset session data
//           $prefixLength = strlen($this->prefix);
//           foreach ($_SESSION as $key => $value) {
//             $compare = substr($key, 0, $prefixLength);
//             if ($compare == $this->prefix) {
//               unset($_SESSION[$key]);
//             }
//           }
//           $this['ip'] = $clientIp;
//         }
//       }
//       else {
//         $this['ip'] = $clientIp;
//       }
//     }
    if (!isset($this['messages'])) {
      $this['messages'] = array();
    }
    foreach ($this['messages'] as $uid => $flash) {
      $this->messages[$uid] = Flash::fromArray($this, $flash);
    }
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
  
  /**
   * Flash a notice to the user
   * @param string $message Message
   * @param string $label Message label, i.e. 'Alert'
   */
  public function notice($message, $label = null) {
    $flash = new Flash($this, $message, 'notice', $label);
    $this->messages[$flash->uid] = $flash;
    $messages = $this['messages'];
    $messages[$flash->uid] = $flash->toArray();
    $this['messages'] = $messages;
  }

  /**
   * Flash an alert to the user
   * @param string $message Message
   * @param string $label Message label, i.e. 'Alert'
   */
  public function alert($message, $label = null) {
    $flash = new Flash($this, $message, 'alert', $label);
    $this->messages[$flash->uid] = $flash;
    $messages = $this['messages'];
    $messages[$flash->uid] = $flash->toArray();
    $this['messages'] = $messages;
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
