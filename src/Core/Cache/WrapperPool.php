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
  public function clear() {
    return $this->pool->clear();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys) {
    $this->pool->deleteItems($keys);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItem $item) {
    $this->pool->save($item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function saveDeferred(CacheItem $item) {
    $this->pool->saveDeferred($item);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->pool->commit();
    return $this;
  }
}