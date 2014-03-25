<?php
/**
 * Provides access to cookies.
 * 
 * Implements arrayaccess, so the []-operator can be used
 * to get and set cookies.
 * @package Jivoo\Routing
 */
class Cookies implements arrayaccess {

  /**
   * @var string Cookie prefix
   */
  private $prefix = '';

  /**
   * @var unknown
   */
  private $cookies = array();
  
  private $basePath = '/'; 

  /**
   * Constructor
   * @param array $cookies Key/value pairs, e.g. from $_COOKIE.
   * @param string $prefix Cookie prefix to use.
   * @param string $basePath Default path for cookies to be available on
   */
  public function __construct($cookies = array(), $prefix = '', $basePath = '/') {
    $this->cookies = $cookies;
    $this->prefix = $prefix;
    $this->basePath = $basePath;
  }

  /**
   * Create a cookie.
   * @param string $name Name of cookie
   * @param string $value Value of cookie
   * @param int $expire The time the cookie expires as a UNIX timestamp, default
   * is a year
   * @param string $path The path on the server in which the cookie will be available on.
   */
  public function setCookie($name, $value, $expire = null, $path = null) {
    if (!isset($path)) {
      $path = $this->basePath;
    }
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

  /**
   * Whether or not a cookie exists
   * @param string $name Name of cookie
   * @return bool True if it exists, false otherwise
   */
  public function offsetExists($name) {
    return isset($this->cookies[$this->prefix . $name]);
  }

  /**
   * Gets the value of a cookie
   * @param string $name Name of cookie
   * @return string Value of cookie
   */
  public function offsetGet($name) {
    return $this->cookies[$this->prefix . $name];
  }

  /**
   * Set the value of a cookie
   * @param string $name Name of cookie
   * @param string $value Value of cookie
   */
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $this->setCookie($name, $value);
    }
  }

  /**
   * Delete a cookie
   * @param string $name Name of cookie
   */
  public function offsetUnset($name) {
    $this->setCookie($name, '', time());
    unset($this->cookies[$this->prefix . $name]);
  }

}
