<?php
namespace Jivoo\Core\Parse;

use Jivoo\TestCase;

class ParseInputTest extends TestCase {
  public function testEmpty() {
    $input = new ParseInput(array());
    $this->assertNull($input->peek());
    $this->assertNull($input->peek(1));
    $this->assertNull($input->peek(10));
    $this->assertNull($input->pop());
    $this->assertFalse($input->accept('a'));
    $this->assertThrows('Jivoo\Core\Parse\ParseException', function() use($input) {
      $input->expect('a');
    });
  }
  
  public function testPop() {
    $input = new ParseInput(array('a', 'a', 'b', 'b'));
    $this->assertEquals('a', $input->peek());
    $this->assertEquals('a', $input->peek(1));
    $this->assertEquals('b', $input->peek(2));
    $this->assertEquals('b', $input->peek(3));
    $this->assertNull($input->peek(4));
    $this->assertEquals('a', $input->pop());
    $this->assertEquals('a', $input->peek());
    $this->assertEquals('b', $input->peek(1));
    $this->assertEquals('b', $input->peek(2));
    $this->assertNull($input->peek(3));
  }
  
  public function testAccept() {
    $input = new ParseInput(array('a'));
    $this->assertTrue($input->accept('a'));
    $this->assertFalse($input->accept('a'));
    $this->assertNull($input->peek());
  }
}