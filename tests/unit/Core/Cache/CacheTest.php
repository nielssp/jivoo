<?php

namespace Jivoo\Core\Cache;

abstract class CacheTest extends \Jivoo\Test {
  protected function _before() {}

  protected function _after() {}
  
  /**
   * @return Cache 
   */
  abstract protected function getCache();
  
  public function testImplements() {
    $this->assertInstanceOf('Jivoo\Core\Cache\Cache', $this->getCache());
  }
  
  public function testGet() {
    $cache = $this->getCache();
    $this->assertNull($cache->get($key));
  }
  
}