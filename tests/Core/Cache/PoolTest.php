<?php

namespace Jivoo\Core\Cache;

abstract class PoolTest extends \Jivoo\TestCase {
  protected function _before() {}

  protected function _after() {}
  
  /**
   * @return Pool Empty cache pool for testing.
   */
  abstract protected function getPool();
  
  public function testImplements() {
    $this->assertInstanceOf('Jivoo\Core\Cache\Pool', $this->getPool());
  }
  
  protected function assertMiss(Pool $pool, $key) {
    $this->assertNull($pool->get($key));
    $this->assertNull($pool->getItem($key)->get());
    $this->assertFalse($pool->getItem($key)->isHit());
    $this->assertFalse($pool->hasItem($key));
  }
  
  protected function assertHit($expected, Pool $pool, $key) {
    $this->assertEquals($expected, $pool->get($key));
    $this->assertEquals($expected, $pool->getItem($key)->get());
    $this->assertTrue($pool->getItem($key)->isHit());
    $this->assertTrue($pool->hasItem($key));
  }
  
  public function testEmptyPool() {
    $pool = $this->getPool();
    $this->assertMiss($pool, 'foo');
    $this->assertEquals('foo', $pool->getItem('foo')->getKey());
    
    $this->assertEmpty($pool->getItems(array()));
    $this->assertCount(1, $pool->getItems(array('foo')));
    $this->assertCount(2, $pool->getItems(array('foo', 'bar')));
  }
  
  public function testSave() {
    $pool = $this->getPool();
    $this->assertTrue($pool->save($pool->getItem('foo')->set('bar')));

    $this->assertHit('bar', $pool, 'foo');

    $this->assertTrue($pool->set('foobar', 'baz'));

    $this->assertHit('bar', $pool, 'foo');
    $this->assertHit('baz', $pool, 'foobar');
  }
  
  public function testDelete() {
    $pool = $this->getPool();
    $pool->save($pool->getItem('foo')->set('bar'));
    
    $pool->delete('foo');
    $this->assertMiss($pool, 'foo');

    $pool->save($pool->getItem('foo')->set('bar'));
    $pool->save($pool->getItem('foobar')->set('baz'));
    
    $this->assertTrue($pool->deleteItems(array('foo', 'foobar')));

    $this->assertMiss($pool, 'foo');
    $this->assertMiss($pool, 'foobar');
  }
  
  public function testClear() {
    $pool = $this->getPool();

    $pool->save($pool->getItem('foo')->set('bar'));
    $pool->save($pool->getItem('foobar')->set('baz'));
    
    $this->assertTrue($pool->clear());

    $this->assertMiss($pool, 'foo');
    $this->assertMiss($pool, 'foobar');
  }
  
  public function testSaveDeferred() {
    $pool = $this->getPool();
    $this->assertTrue($pool->saveDeferred($pool->getItem('foo')->set('bar')));
    $this->assertTrue($pool->saveDeferred($pool->getItem('foobar')->set('baz')));

    $this->assertTrue($pool->commit());

    $this->assertHit('bar', $pool, 'foo');
    $this->assertHit('baz', $pool, 'foobar');
  }
  
  public function testExpiration() {
    $pool = $this->getPool();
    
    $item = $pool->getItem('foo')->set('bar');
    $item->expiresAfter(-1);
    $pool->save($item);
    $this->assertMiss($pool, 'foo');

    $item = $pool->getItem('foo')->set('bar');
    $item->expiresAt(\DateTime::createFromFormat('U', 0));
    $pool->save($item);
    $this->assertMiss($pool, 'foo');

    $pool->set('foo', 'bar', time() - 1);
    $this->assertMiss($pool, 'foo');
    
    $pool->set('foo', 'bar', \DateTime::createFromFormat('U', 0));
    $this->assertMiss($pool, 'foo');

    $interval = new \DateInterval('PT1S');
    $interval->invert = 1;
    $pool->set('foo', 'bar', $interval);
    $this->assertMiss($pool, 'foo');

    $pool->set('foo', 'bar', 2592001);
    $this->assertMiss($pool, 'foo');

    $pool->set('foo', 'bar', 2592000); // 30 days in the future
    $this->assertHit('bar', $pool, 'foo');

    $item = $pool->getItem('foobar')->set('baz');
    $item->expiresAfter(100);
    $pool->save($item);
    $this->assertHit('baz', $pool, 'foobar');
  }
  
  public function testMutators() {
    $pool = $this->getPool();
    
    $this->assertTrue($pool->add('foo', 'bar'));
    $this->assertHit('bar', $pool, 'foo');
    
    $this->assertFalse($pool->add('foo', 'baz'));
    $this->assertHit('bar', $pool, 'foo');

    $this->assertTrue($pool->replace('foo', 'foobar'));
    $this->assertHit('foobar', $pool, 'foo');
    $this->assertTrue($pool->touch('foo'));
    
    $pool->delete('foo');
    $this->assertFalse($pool->replace('foo', 'foobar'));
    $this->assertMiss($pool, 'foo');
    $this->assertFalse($pool->touch('foo'));
    
    $this->assertEquals(0, $pool->increment('foo'));
    $this->assertEquals(1, $pool->increment('foo'));
    $this->assertEquals(2, $pool->increment('foo'));
    $this->assertEquals(1, $pool->decrement('foo'));
    $this->assertEquals(0, $pool->decrement('foo'));
    $this->assertHit(0, $pool, 'foo');
    
    $this->assertEquals(10, $pool->increment('bar', 1, 10));
    $this->assertEquals(20, $pool->increment('bar', 10));
    $this->assertHit(20, $pool, 'bar');

    $this->assertEquals(10, $pool->decrement('foobar', 1, 10));
    $this->assertHit(10, $pool, 'foobar');
    
  }
}
