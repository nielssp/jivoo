<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\InvalidPropertyException;

/**
 * Session key-value store.
 *
 * Implements ArrayAccess, so the []-operator can be used
 * to get and set session values.
 * 
 * @property-read string $id Session id.
 * @property-read FlashMap $flash Flash messages.
 * @deprecated
 */
class Session implements \ArrayAccess {
  
  /**
   * @var bool Whether or not session is open.
   */
  private $open = true;
  
  /**
   * @var string Session prefix.
   */
  private $prefix = '';
  
  /**
   * @var array Associative array of uid and Flash-objects.
   */
  private $messages = array();
  
  /**
   * @var FlashMap Flash messages.
   */
  private $flash;
  
  /**
   * Construct session storage.
   * @param string $prefix Session prefix to use.
   * @param string $name Session cookie name.
   * @param bool $secure Whether to enable Secure flag on session cookie.
   * @param bool $httpOnly Whether to enable HttpOnly flag on session cookie.
   */
  public function __construct($prefix = '', $name = null, $secure = false, $httpOnly = true) {
    $params = session_get_cookie_params();
    session_set_cookie_params(
      $params['lifetime'],
      $params['path'],
      $params['domain'],
      $secure, $httpOnly
    );
    session_name($name);
    session_start();
    $this->prefix = $prefix;
    $this->flash = new FlashMap($this);
  }
  
  /**
   * Destruct. Saves flash messages.
   */
  public function __destruct() {
    if ($this->id != '')
      $this->flash->save();
  }
  
  /**
   * Get value of property.
   * @param string $property Name of property.
   * @return mixed Value.
   * @throws InvalidPropertyException If property unknown.
   */
  public function __get($property) {
    switch ($property) {
      case 'id':
        return session_id();
      case 'flash':
        return $this->flash;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }
  
  /**
   * Convert session data to associative array.
   * @return array Aassociative array.
   */
  public function toArray() {
    return $_SESSION;
  }
  
  /**
   * Reopen a closed session.
   * @return boolean True on success, false on failure.
   */
  public function open() {
    $this->open = true;
    return session_start();
  }
  
  /**
   * Close session and store data. Unlocks session data.
   * Allows other scripts to use session.
   */
  public function close() {
    $this->open = false;
    $this->flash->save();
    session_write_close();
  }
  
  /**
   * Replace session id with new one.
   * @return boolean True on success, false on failure.
   */
  public function regenerate() {
    return session_regenerate_id();
  }
  
  /**
   * Whether or not a key exists.
   * @param string $name Key.
   * @return bool True if it does, false otherwise.
   */
  public function offsetExists($name) {
    return isset($_SESSION[$this->prefix . $name]);
  }
  
  /**
   * Get value, return as reference.
   * @param string $name Key.
   * @return mixed Value.
   */
  private function &get($name) {
    return $_SESSION[$this->prefix . $name];
  }

  /**
   * Get a value.
   * @param string $name Key.
   * @return mixed Value.
   */
  public function offsetGet($name) {
    return $this->get($name);
  }

  /**
   * Associate a value with a key.
   * @param string $name Key.
   * @param mixed $value Value.
   */
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $_SESSION[$this->prefix . $name] = $value;
    }
  }

  /**
   * Delete a key.
   * @param string $name Key.
   */
  public function offsetUnset($name) {
    unset($_SESSION[$this->prefix . $name]);
  }

}
