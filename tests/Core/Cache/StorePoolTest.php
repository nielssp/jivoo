<?php
namespace Jivoo\Core\Cache;

use Jivoo\Core\Store\JsonStore;

class StorePoolTest extends PoolTest {
  
  private $file = 'tests/_data/cache.json';
  
  protected function setUp() {
  }

  protected function tearDown() {
    unlink($this->file);
  }

  protected function getPool() {
    $store = new JsonStore($this->file);
    $store->touch();
    return new StorePool($store);
  }
}
