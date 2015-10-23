<?php

namespace Jivoo\Models;

class DataTypeTest extends \Jivoo\Test {

  protected function _before() {}

  protected function _after() {}

  public function testDetectType() {
    $this->assertTrue(DataType::detectType(true)->isBoolean());
    $this->assertTrue(DataType::detectType(false)->isBoolean());
    $this->assertTrue(DataType::detectType(132)->isInteger());
    $this->assertTrue(DataType::detectType(132.2)->isFloat());
    $this->assertTrue(DataType::detectType(array(1))->isObject());
    $this->assertTrue(DataType::detectType((object)array('a' => 2))->isObject());
    $this->assertTrue(DataType::detectType('foo')->isText());
  }
}
