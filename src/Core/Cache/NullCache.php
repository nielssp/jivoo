<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Jivoo\Core\Store\IStore;

/**
 * A cache that doesn't store anything. Can be used to disable caching.
 */
class NullCache implements ICache {
  /**
   * {@inheritdoc}
   */
  public function get($key) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value, $expiration = 0) 
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function add($key, $value, $expiration = 0) {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function replace($key, $value, $expiration = 0) {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function touch($key, $expiration = 0) {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key, $offset = 1, $init = 0) {
    return $init + $offset;
  }

  /**
   * {@inheritdoc}
   */
  public function decrement($key, $offset = 1, $init = 0) {
    return $init - $offset;
  }
  
  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    return false;
  }
}