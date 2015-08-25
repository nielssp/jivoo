<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Cache;

use Psr\Cache\CacheItemPoolInterface as CacheItemPool;

class Cache {
  private $pools = array();
  
  private $defaultProvider = null;
  
  public function __get($pool) {
    return $this->getPool($pool);
  }
  
  public function __set($name, CacheItemPool $pool) {
    if (!($pool instanceof Pool))
      $pool = new WrapperPool($pool);
    $this->pools[$name] = $pool;
  }
  
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