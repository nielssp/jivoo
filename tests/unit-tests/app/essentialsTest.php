<?php
class essentialsTest extends PHPUnit_Framework_TestCase {

  public function testTr() {
    $this->assertEquals('Hello, World!', tr('Hello, World!'));
    $this->assertEquals('Hello, World!', tr('Hello, %1!', 'World'));
    $this->assertEquals('Hello, World!', tr('%2, %1!', 'World', 'Hello'));
  }

  public function testTrl() {
    $list = array('test1', 'test2', 'test3');

    $expected = 'I am looking at the objects test1, test2 and test3';
    $actual = trl('I am looking at the %1 %l', 'I am looking at the %1s %l',
      ', ', ' and ', $list, 'object');
    $this->assertEquals($expected, $actual);
  }

  public function testTrn() {
    $expected = 'There are 3 pieces of paper in the bin';
    $actual = trn('There are %1 piece of %2 in the %3',
      'There are %1 pieces of %2 in the %3', 3, 'paper', 'bin');
    $this->assertEquals($expected, $actual);
  }

  public function testFdate() {
    $this->assertEquals(date('Y-m-d'), fdate());
  }

  public function testFtime() {
    $this->assertEquals(date('H:m:i'), ftime());
  }

  public function testTdate() {
    $this->assertEquals(date('Y-m-d'), tdate('Y-m-d'));
  }
}
