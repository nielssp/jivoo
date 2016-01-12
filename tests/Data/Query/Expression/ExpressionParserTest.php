<?php

namespace Jivoo\Data\Query\Expression;

class ExpressionParserTest extends \Jivoo\TestCase {

  protected function _before() {}

  protected function _after() {}

  public function testLex() {
    $expr = '1 = 15.2';
    $tokens = ExpressionParser::lex($expr, array())->toArray();
    $this->assertCount(3, $tokens);
    $this->assertEquals('literal', $tokens[0][0]);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $tokens[0][1]);
    $this->assertEquals(1, $tokens[0][1]->value);
    $this->assertEquals('operator', $tokens[1][0]);
    $this->assertEquals('=', $tokens[1][1]);
    $this->assertEquals('literal', $tokens[2][0]);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $tokens[2][1]);
    $this->assertEquals(15.2, $tokens[2][1]->value);

    $expr = '%s';
    $tokens = ExpressionParser::lex($expr, array('test'))->toArray();
    $this->assertCount(1, $tokens);
    $this->assertEquals('literal', $tokens[0][0]);
    $this->assertInstanceOf('Jivoo\Data\Query\Expression\Literal', $tokens[0][1]);
    $this->assertEquals('test', $tokens[0][1]->value);
  }
}
