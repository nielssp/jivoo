<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Jivoo\Core\Store\Store;

/**
 * A cache that stores values in an {@see Store}. 
 */
class StoreCache implements Cache {
  /**
   * @var Store
   */
  private $store;
  
  /**
   * @var array|null
   */
  private $data = null;
  
  /**
   * Construct store cache.
   * @param Store $store Store.
   */
  public function __construct(Store $store) {
    $this->store = $store;
  }
  
  public function __destruct() {
    $this->write();
  }
  
  /**
   */
  private function read() {
    if ($this->store->isOpen()) {
      $this->data = $this->store->read();
    }
    else {
      $this->store->open(false);
      $this->data = $this->store->read();
      $this->store->close();
    }
  }

  /**
   */
  private function write() {
    if (!isset($this->data))
      return;
    $this->store->open(true);
    $this->store->write($this->data);
    $this->store->close();
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    if (!isset($this->data))
      $this->read();
    if (isset($this->data[$key])) {
      if (!is_array($this->data[$key])) {
        unset($this->data[$key]);
      }
      else {
        if (!isset($this->data[$key][0]) or !isset($this->data[$key][1]))
          unset($this->data[$key]);
        else if ($this->data[$key][1] != 0 and $this->data[$key][1] < time())
          unset($this->data[$key]);
        else
          return $this->data[$key][0];
      }
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value, $expiration = 0) {
    if (!isset($this->data))
      $this->read();
    if ($expiration != 0 and $expiration <= 2592000)
      $expiration = time() + $expiration;
    $this->data[$key] = array($value, $expiration);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function add($key, $value, $expiration = 0) {
    if ($this->get($key) === null)
      return $this->set($key, $value, $expiration);
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function replace($key, $value, $expiration = 0) {
    if ($this->get($key) !== null)
      return $this->set($key, $value, $expiration);
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function touch($key, $expiration = 0) {
    $value = $this->get($key);
    if ($value !== null)
      return $this->set($key, $value, $expiration);
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key, $offset = 1, $init = 0) {
    $value = $this->get($key);
    if (!is_numeric($value))
      $value = $init;
    $value += $offset;
    $this->set($key, $value);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function decrement($key, $offset = 1, $init = 0) {
    $value = $this->get($key);
    if (!is_numeric($value))
      $value = $init;
    $value -= $offset;
    $this->set($key, $value);
    return $value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    if ($key == '') {
      $this->data = array();
      return true;
    }
    if (substr($key, -1) === '.') {
      if (!isset($this->data))
        $this->read();
      $len = strlen($key);
      $success = false;
      foreach ($this->data as $dkey => $value) {
        if (strncmp($key, $dkey, $len) === 0) {
          unset($this->data[$dkey]);
          $success = true;
        }
      }
      return $success;
    }
    $value = $this->get($key);
    if ($value !== null) {
      unset($this->data[$key]);
      return true;
    }
    return false;
  }
}