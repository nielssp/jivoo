<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemInterface as CacheItem;

/**
 * A cache pool that doesn't save any item.
 */
class NullPool extends PoolBase {
  /**
   * {@inheritdoc}
   */
  public function getItem($key) {
    return new NullItem($key);
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    return true;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(array $keys) {
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function save(CacheItem $item) {
    return $this;
  }
}