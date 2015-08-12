<?php

namespace Jivoo\Core\Store;

class StateMapTest extends \Jivoo\Test {

  private $dir = 'tests/_data/state';
  private $state;
  
  protected function _before() {
    $this->state = new StateMap($this->dir);
  }

  protected function _after() {
    $this->tester->deleteDir($this->dir);
  }
  
  public function testRead() {
    try {
      $this->state['test'];
      $this->fail('NotOpenException not thrown');
    }
    catch (NotOpenException $e) { }
    
    $s = $this->state->read('test');
    $this->assertEquals(array(), $s->toArray());
    $this->assertTrue(isset($this->state['test']));
    $this->assertTrue($this->state->isOpen('test'));
    $this->assertFalse($this->state->isMutable('test'));
    $this->assertTrue($s->isOpen());
    $this->assertFalse($s->isMutable());
    $this->assertSame($s, $this->state->read('test'));
    $this->assertSame($s, $this->state['test']);
    unset($this->state['test']);
    $this->assertFalse($this->state->isOpen('test'));
    $this->assertFalse($s->isOpen());
  }
  
  public function testWrite() {
    $s1 = $this->state->read('test');
    $s2 = $this->state->write('test');
    $this->assertTrue($this->state->isMutable('test'));
    $this->assertNotSame($s1, $s2);
    $this->assertFalse($s1->isOpen());
    $this->assertTrue($s2->isOpen());
    $this->assertTrue($s2->isMutable());
    
    $this->assertEquals(array('test'), $this->state->closeAll());
  }
}