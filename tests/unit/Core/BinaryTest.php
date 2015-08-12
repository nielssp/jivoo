<?php
namespace Jivoo\Core;

class BinaryTest extends \Jivoo\Test {
 
  public function testLength() {
    $this->assertEquals(0, Binary::length(''));
    $this->assertEquals(1, Binary::length('a'));
    $this->assertEquals(2, Binary::length('å'));
    $this->assertEquals(3, Binary::length('♥'));
    $this->assertEquals(6, Binary::length('♥♥'));
    $this->assertEquals(7, Binary::length('♥a♥'));
  }
  
  public function testSlice() {
    $this->assertEquals('', Binary::slice('', 0));
    $this->assertEquals('abcde', Binary::slice('abcde', 0));
    $this->assertEquals('a', Binary::slice('abcde', 0, 1));
    $this->assertEquals('ab', Binary::slice('abcde', 0, 2));
    $this->assertEquals('bc', Binary::slice('abcde', 1, 2));
    $this->assertEquals('abcd', Binary::slice('abcde', 0, -1));
    $this->assertEquals('abc', Binary::slice('abcde', 0, -2));
    $this->assertEquals('bc', Binary::slice('abcde', 1, -2));
    $this->assertEquals('e', Binary::slice('abcde', -1));
    $this->assertEquals('de', Binary::slice('abcde', -2));
    $this->assertEquals('d', Binary::slice('abcde', -2, -1));

    $this->assertEquals("\xe2\x99\xa5\xc3\xa5", Binary::slice("♥å", 0));
    $this->assertEquals("\xe2", Binary::slice('♥å', 0, 1));
    $this->assertEquals("\xe2\x99", Binary::slice('♥å', 0, 2));
    $this->assertEquals("\x99\xa5", Binary::slice('♥å', 1, 2));
    $this->assertEquals("\xe2\x99\xa5\xc3", Binary::slice('♥å', 0, -1));
    $this->assertEquals("\xe2\x99\xa5", Binary::slice('♥å', 0, -2));
    $this->assertEquals("\x99\xa5", Binary::slice('♥å', 1, -2));
    $this->assertEquals("\xa5", Binary::slice('♥å', -1));
    $this->assertEquals("\xc3\xa5", Binary::slice('♥å', -2));
    $this->assertEquals("\xc3", Binary::slice('♥å', -2, -1));
  }
}