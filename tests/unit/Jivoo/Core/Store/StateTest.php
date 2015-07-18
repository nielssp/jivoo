<?php

namespace Jivoo\Core\Store;

class StateTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;
  
  private $file;
  private $store;
  
  protected function _before() {
    $this->file = 'tests/_data/state.php';
    $this->store = new PhpStore($this->file);
  }
  
  protected function _after() {
    $this->tester->deleteFile($this->file);
  }
  
  public function testRead() {
    try {
      $state = new State($this->store, false);
      $this->fail('StateInvalidException not thrown');
    }
    catch (StateInvalidException $e) { }
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
    try {
      $state->close();
      $this->fail('StateClosedException not thrown');
    }
    catch (StateClosedException $e) { }
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