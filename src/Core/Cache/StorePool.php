<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Jivoo\Core\Store\Store;
use Psr\Cache\CacheItemInterface as CacheItem;

/**
 * A cache that stores values in an {@see Store}. 
 */
class StorePool extends PoolBase {
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
  public function getItem($key) {
    if (!isset($this->data))
      $this->read();
    if (array_key_exists($key, $this->data)) {
      $value = $this->data[$key][0];
      $expiration = $this->data[$key][1];
      if ($expiration <= time()) {
        unset($this->data[$key]);
      }
      else {
        if ($expiration == 0)
          $expiration = null;
        else
          $expiration = \DateTime::createFromFormat('U', $expiration);
        return new MutableItem($key, $value, true, $expiration);
      }
    }
    return new MutableItem($key, null, false, null);
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $this->data = array();
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys) {
    if (!isset($this->data))
      $this->read();
    foreach ($keys as $key) {
      if (isset($this->data[$key]))
        unset($this->data[$key]);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItem $item) {
    if (!isset($this->data))
      $this->read();
    $expiration = $item->getExpiration();
    if (isset($expiration))
      $expiration = $expiration->getTimestamp();
    else
      $expiration = 0;
    $this->data[$item->getKey()] = array($item->get(), $expiration);
    return $this;
  }
}