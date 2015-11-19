<?php

namespace Jivoo\Core\Store;

class ConfigTest extends \Jivoo\TestCase {

  private $file;
  private $store;

  protected function setUp() {
    $this->file = 'tests/_data/config.php';
    $this->store = new PhpStore($this->file);
    $this->store->touch();
  }
  
  protected function tearDown() {
    unlink($this->file);
  }
  
  public function testRead() {
    $conf = new Config($this->store);
    $this->assertEquals(array(), $conf->toArray());
    
    $data = array(
      'a' => 'foo',
      'b' => array('c' => 'bar'),
      'd' => array(1, 2, array(1, 2), 4)
    );
    
    $this->store->open(true);
    $this->store->write($data);
    $this->store->close();
    
    $conf['a'] = 'bar';
    $conf['b']->reload();
    $this->assertEquals($data, $conf->toArray());
  }
  
  public function testWrite() {
    $data = array(
      'a' => 'foo',
      'b' => array('c' => 'bar'),
    );
    $conf = new Config($this->store);
    $conf->override = $data;
    
    $this->assertEquals($data, $conf->toArray());
    $this->assertTrue($conf->save());
    $this->assertTrue($conf['b']->save());

    $conf = new Config($this->store);
    $this->assertEquals($data, $conf->toArray());
  }
}
