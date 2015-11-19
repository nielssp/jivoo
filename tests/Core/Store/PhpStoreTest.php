<?php

namespace Jivoo\Core\Store;

class PhpStoreTest extends \Jivoo\TestCase {

  private $file;
  private $store;

  protected function setUp() {
    $this->file = 'tests/_data/store.php';
    $this->store = new PhpStore($this->file);
  }

  protected function tearDown() {
    unlink($this->file);
  }
  
  public function testTouch() {
    $this->assertTrue($this->store->touch());
  }
  
  public function testRead() {
    $this->store->touch();
    $this->store->open(false);
    $this->assertEquals(array(), $this->store->read());
    $this->assertTrue($this->store->isOpen());
    $this->assertFalse($this->store->isMutable());
    $this->store->close();
    $this->assertFalse($this->store->isOpen());
    
    file_put_contents($this->file, '<?php return null;');
    $this->store->open(false);
    $store = $this->store;
    $this->assertThrows('Jivoo\Core\Store\AccessException', function() use($store) {
      $store->read();
    });
    $this->store->close();
  } 
  
  public function testWrite() {
    $this->store->touch();
    $data = array(
      'a' => 'foo',
      'b' => array('c' => 'bar'),
      'd' => array(1, 2, array(1, 2), 4)
    );

    $this->store->open(true);
    $this->assertTrue($this->store->isOpen());
    $this->assertTrue($this->store->isMutable());
    $this->store->write($data);
    $this->assertEquals($data, $this->store->read());
    
    $data = array(
      'a' => 'foo',
      'b' => array('c' => 'bar'),
      'd' => array(1, 2, array(1, 2), 4),
      'e' => array('foobar', 'bazbar')
    );
    $this->store->write($data);
    $this->assertEquals($data, $this->store->read());
    $this->store->close();
    
    $this->store->open(false);
    $this->assertEquals($data, $this->store->read());
    $this->store->close();
  }
  
  public function testLocking() { 
    $store2 = new PhpStore($this->file);
    
    $store2->disableBlocking();
    $this->store->open(true);
    $this->assertThrows('Jivoo\Core\Store\LockException', function() use($store2) {
      $store2->open(true);
    });
    $this->assertThrows('Jivoo\Core\Store\LockException', function() use($store2) {
      $store2->open(false);
    });
    $this->store->close();
    $this->store->open(false);
    $this->assertThrows('Jivoo\Core\Store\LockException', function() use($store2) {
      $store2->open(true);
    });
    $store2->open(false);
    $store2->close();
    $this->store->close();
  }
}
