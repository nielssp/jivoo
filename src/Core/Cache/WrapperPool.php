<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemPoolInterface as CacheItemPool;
use Psr\Cache\CacheItemInterface as CacheItem;

/**
 * Wraps a {@see Psr\Cache\CacheItemPoolInterface} with additional features. 
 */
class WrapperPool extends PoolBase {
  /**
   * @var CacheItemPool
   */
  private $pool;
  
  /**
   * Construct wrapper.
   * @param CacheItemPool $pool Cache pool.
   */
  public function __construct(CacheItemPool $pool) {
    $this->pool = $pool;
  }

  /**
   * {@inheritdoc}
   */
  public function getItem($key) {
    return $this->pool->getItem($key);
  }

  /**
   * {@inheritdoc}
   */
  public function getItems(array $keys = array()) {
    return $this->pool->getItem($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function hasItem($key) {
    return $this->pool->hasItem($key);
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    return $this->pool->clear();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItem($key) {
    return $this->pool->deleteItem($key);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys) {
    return $this->pool->deleteItems($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItem $item) {
    return $this->pool->save($item);
  }

  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItem $item) {
    return $this->pool->saveDeferred($item);
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    return $this->pool->commit();
  }
}