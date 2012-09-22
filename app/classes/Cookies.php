<?php
/**
 * Provides access to cookies.
 * @package PeanutCMS
 */
class Cookies implements arrayaccess {
  
  private $prefix = '';
  
  private $cookies = array();

  /**
   * Constructor.
   * @param array $cookies Key/value pairs, e.g. from $_COOKIE.
   * @param string $prefix Cookie prefix to use.
   */
  public function __construct($cookies = array(), $prefix = '') {
    $this->cookies = $cookies;
    $this->prefix = $prefix;
  }

  /**
   * Create a cookie.
   * @param string $name Name of cookie
   * @param string $value Value of cookie
   * @param int $expire The time the cookie expires as a UNIX timestamp
   * @param string $path The path on the server in which the cookie will be available on.
   * @return void
   */
  public function setCookie($name, $value, $expire = null, $path = WEBPATH) {
    if (!isset($expire)) {
      $expire = time() + 60 * 60 * 24 * 365;
    }
    $this->cookies[$this->prefix . $name] = $value;
    try {
      setcookie($this->prefix . $name, $value, $expire, $path);
    }
    catch (Exception $e) {
    }
  }

  public function offsetExists($name) {
    return isset($this->cookies[$this->prefix . $name]);
  }
  
  public function offsetGet($name) {
    return $this->cookies[$this->prefix . $name];
  }
  
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $this->setCookie($name, $value);
    }
  }
  
  public function offsetUnset($name) {
    $this->setCookie($name, '', time());
    unset($this->cookies[$this->prefix . $name]);
  }
  
}
