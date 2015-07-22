<?php

namespace Jivoo\Jtk\Menu;

class MenuActionTest extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}

  public function testProperties() {
    $action = new MenuAction('Test', 'snippet:Test', 'some-icon');
    $this->assertEquals('snippet:Test', $action->getRoute());
    $this->assertNotNull($action->label);
    $this->assertNotNull($action->icon);
  }
}
