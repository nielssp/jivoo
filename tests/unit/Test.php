<?php

namespace Jivoo;

abstract class Test extends \Codeception\TestCase\Test {

  /**
   *
   * @var \UnitTester
   */
  protected $tester;

  protected function _before() {}

  protected function _after() {}
  
  /**
   * Assert that an exception is thrown.
   * @param string $expected Expected exception class or interface.
   * @param callable $callable Callable that should throw exception.
   */
  protected function assertThrows($expected, $callable) {
    try {
      $callable();
      $this->fail('Exception of type ' . $expected . ' not thrown');
    }
    catch (\Exception $actual) {
      $this->assertInstanceOf($expected, $actual);
    }
  }
}