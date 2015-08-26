<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemPoolInterface as CacheItemPool;

/**
 * A collection of cache pools.
 */
class Cache {
  /**
   * @var Pool[]
   */
  private $pools = array();
  
  /**
   * @var callable|null
   */
  private $defaultProvider = null;
  
  /**
   * Get a pool. See {@see getPool}.
   * @param string $pool Pool name.
   * @return Pool Cache pool.
   */
  public function __get($pool) {
    return $this->getPool($pool);
  }
  
  /**
   * Set cache pool.
   * @param string $name Pool name.
   * @param CacheItemPool $pool Cache pool.
   */
  public function __set($name, CacheItemPool $pool) {
    if (!($pool instanceof Pool))
      $pool = new WrapperPool($pool);
    $this->pools[$name] = $pool;
  }
  
  /**
   * Set default provider of cache pools.
   * @param callable $provider A function accepting a pool name (string) and 
   * returning an instance of {@see Pool}. {@see WrapperPool} may be used to
   * wrap {@see CacheItemPool} instances.
   */
  public function setDefaultProvider($provider) {
    $this->defaultProvider = $provider;
  }

  /**
   * Get a pool. If a pool with that name does not exist, one will be generated
   * using the default provider (see {@see setDefaultProvider}). If a default
   * provider hasn't been set, a {@see NullPool} will be returned.
   * @param string $pool Pool name.
   * @return Pool Cache pool.
   */
  public function getPool($pool) {
    if (!isset($this->pools[$pool])) {
      if (isset($this->defaultProvider)) {
        $this->pools[$pool] = call_user_func($this->defaultProvider, $pool);
      }
      else {
        $this->pools[$pool] = new NullPool();
      }
    }
    return $this->pools[$pool];
  }
}