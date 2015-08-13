<?php

namespace Jivoo\Core\Cache;

abstract class ICacheTest extends \Jivoo\Test {
  protected function _before() {}

  protected function _after() {}
  
  /**
   * @return ICache 
   */
  abstract protected function getCache();
  
  public function testImplements() {
    $this->assertInstanceOf('Jivoo\Core\Cache\ICache', $this->getCache());
  }
  
  public function testGet() {
    $cache = $this->getCache();
    $this->assertNull($cache->get($key));
  }
  
}