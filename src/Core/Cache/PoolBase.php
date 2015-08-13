<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemInterface as CacheItem;

abstract class PoolBase implements Pool {
  /**
   * {@inheritdoc}
   */
  public function getItems(array $keys = array()) {
    $items = array();
    foreach ($keys as $key)
      $items[] = $this->getItem($key);
    return $items;
  }
  
  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItem $item) {
    $this->save();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function get($key) {
    return $this->getItem($key)->get();
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value, $expiration = 0) {
    $item = $this->getItem($key);
    $item->set($value);
    $item->expiresAt($expiration);
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function add($key, $value, $expiration = 0) {
    $item = $this->getItem($key);
    if ($this->isHit())
      return false;
    $item->set($value);
    $item->expiresAt($expiration);
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function replace($key, $value, $expiration = 0) {
    $item = $this->getItem($key);
    if (!$this->isHit())
      return false;
    $item->set($value);
    $item->expiresAt($expiration);
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function touch($key, $expiration = 0) {
    $item = $this->getItem($key);
    if (!$this->isHit())
      return false;
    $item->expiresAt($expiration);
    $this->save($item);
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function increment($key, $offset = 1, $init = 0) {
    $item = $this->getItem($key);
    if ($this->isHit())
      $value = $item->get();
    else
      $value = $init;
    $value += $offset;
    $item->set($value);
    $this->save($item);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function decrement($key, $offset = 1, $init = 0) {
    $item = $this->getItem($key);
    if ($this->isHit())
      $value = $item->get();
    else
      $value = $init;
    $value -= $offset;
    $item->set($value);
    $this->save($item);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    $this->deleteItems(array($key));
  }
}