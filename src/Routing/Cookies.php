<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Provides access to cookies.
 * 
 * Implements ArrayAccess, so the []-operator can be used
 * to get and set cookies.
 */
class Cookies implements \ArrayAccess {

  /**
   * @var string Cookie prefix.
   */
  public $prefix = '';

  /**
   * @var string[] Cookie values.
   */
  private $cookies = array();
  
  /**
   * @var string Base path for cookies.
   */
  private $basePath = '/'; 

  /**
   * Opens the cookie jar.
   * @param string[] $cookies Associative array of cookies, e.g. from $_COOKIE.
   * @param string $prefix Cookie prefix to use.
   * @param string $basePath Default path for cookies to be available on.
   */
  public function __construct($cookies = array(), $prefix = '', $basePath = '/') {
    $this->cookies = $cookies;
    $this->prefix = $prefix;
    $this->basePath = $basePath;
  }
  
  /**
   * Convert cookie data to associative array.
   * @return array Aassociative array.
   */
  public function toArray() {
    return $this->cookies;
  }

  /**
   * Create a cookie.
   * @param string $name Name of cookie.
   * @param string $value Value of cookie.
   * @param int $expire The time the cookie expires as a UNIX timestamp, default
   * is a year.
   * @param string $path The path on the server in which the cookie will be available on.
   * @param string|null $domain Domain.
   * @param bool $secure Whether to set Secure flag.
   * @param bool $httpOnly Whether to set HttpOnly flag. 
   */
  public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = false, $httpOnly = false) {
    if (!isset($path)) {
      $path = $this->basePath;
    }
    if (!isset($expire)) {
      $expire = time() + 60 * 60 * 24 * 365;
    }
    $this->cookies[$this->prefix . $name] = $value;
    try {
      setcookie($this->prefix . $name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
    catch (\Exception $e) {
    }
  }
  
  /**
   * Whether or not a cookie exists.
   * @param string $name Name of cookie.
   * @return bool True if it exists, false otherwise.
   */
  public function offsetExists($name) {
    return isset($this->cookies[$this->prefix . $name]);
  }

  /**
   * Gets the value of a cookie.
   * @param string $name Name of cookie.
   * @return string Value of cookie.
   */
  public function offsetGet($name) {
    return $this->cookies[$this->prefix . $name];
  }

  /**
   * Set the value of a cookie.
   * @param string $name Name of cookie.
   * @param string $value Value of cookie.
   */
  public function offsetSet($name, $value) {
    if (is_null($name)) {
    }
    else {
      $this->setCookie($name, $value);
    }
  }

  /**
   * Delete a cookie.
   * @param string $name Name of cookie.
   */
  public function offsetUnset($name) {
    $this->setCookie($name, '', time());
    unset($this->cookies[$this->prefix . $name]);
  }

}
