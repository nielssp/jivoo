<?php

namespace Jivoo\Core;

class MapTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}

  public function testGettersAndSetters() {
    $map = new Map(array('test' => 1));
    $this->assertEquals(1, $map->test);
    $map->test2 = 2;
    $this->assertEquals(2, $map->test2);
    $this->assertTrue(isset($map->test));
    unset($map->test);
    $this->assertFalse(isset($map->test));

    $this->assertEquals(2, $map['test2']);
    $map['test3'] = 3;
    $this->assertEquals(3, $map['test3']);
    $this->assertTrue(isset($map['test2']));
    unset($map['test2']);
    $this->assertFalse(isset($map['test2']));
    $map[] = 5;
    $this->assertEquals(1, count($map));

    try {
      $map->test;
      $this->fail('MapKeyInvalidException not thrown');
    }
    catch (MapKeyInvalidException $e) {}
    
    foreach ($map as $key => $value) {
      $this->assertEquals('test3', $key);
      $this->assertEquals(3, $value);
    }
  }
  
  public function testReadOnly() {
    $map = new Map(array('test' => 1), true);
    $this->assertTrue($map->isReadOnly());

    try {
      $map->test = 5;
      $this->fail('MapReadOnlyException not thrown');
    }
    catch (MapReadOnlyException $e) {}
    
    $this->assertEquals(1, $map->test);
    
    try {
      unset($map->test);
      $this->fail('MapReadOnlyException not thrown');
    }
    catch (MapReadOnlyException $e) {}
    
    $this->assertTrue(isset($map->test));
  }
}
