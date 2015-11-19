<?php

namespace Jivoo\Core\Store;

class StateTest extends \Jivoo\TestCase {
  
  private $file;
  private $store;
  
  protected function setUp() {
    $this->file = 'tests/_data/state.php';
    $this->store = new PhpStore($this->file);
  }
  
  protected function tearDown() {
    unlink($this->file);
  }
  
  public function testRead() {
    $store = $this->store;
    $this->assertThrows('Jivoo\Core\Store\AccessException', function() use($store) {
      $state = new State($store, false);
    });
    $this->store->touch();
    $state = new State($this->store, false);
    $this->assertEquals(array(), $state->toArray());

    $state2 = new State($this->store, false);
    $this->assertEquals(array(), $state2->toArray());
    $state2->close();
    
    $this->assertTrue($state->isOpen());
    $this->assertFalse($state->isMutable());
    
    $state->close();

    $this->assertFalse($state->isOpen());
    $this->assertThrows('Jivoo\Core\Store\NotOpenException', function() use($state) {
      $state->close();
    });
  }
  
  public function testWrite() {
    $this->store->touch();
    $state = new State($this->store, true);
    $this->assertTrue($state->isOpen());
    $this->assertTrue($state->isMutable());

    $data = array(
      'a' => 'foo',
      'b' => array('c' => 'bar'),
      'd' => array(1, 2, array(1, 2), 4)
    );
    $state->override = $data;
    
    $this->assertEquals($data, $state->toArray());

    $state->defaults = array(
      'e' => array('f' => 2)
    );
    
    $state->close();
    
    $state = new State($this->store, false);
    $this->assertEquals($data, $state->toArray());
    $state->close();
  }
}
