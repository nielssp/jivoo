<?php

namespace Jivoo\Jtk\Menu;

class MenuItemTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}

  public function testProperties() {
    $item = $this->getMockForAbstractClass('Jivoo\Jtk\Menu\MenuItem');
    $this->assertFalse(isset($item->label));
    $this->assertNull($item->label);
    unset($item->label);
    $this->assertFalse(isset($item->label));
    $this->assertNull($item->label);
    $item->label = 'Test';
    $this->assertEquals('Test', $item->label);
    $this->assertTrue(isset($item->label));
    unset($item->label);
    $this->assertFalse(isset($item->label));
    $this->assertNull($item->label);
  }
}
