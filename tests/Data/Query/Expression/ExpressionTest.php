<?php

namespace Jivoo\Data\Query\Expression;

abstract class ExpressionTest extends \Jivoo\TestCase {
  protected function _before() {}

  protected function _after() {}
  
  /**
   * @return Expression An empty expression.
   */
  abstract protected function getExpression();
  
  public function testImplements() {
    $this->assertInstanceOf('Jivoo\Data\Query\Expression', $this->getExpression());
  }
  
  protected function testEmptyExpression() {
    $expression = $this->getExpression();
    $this->assertEmpty($expression->getString());
    $this->assertEmpty($expression->getVars());
  }
  
  // TODO: rest
}
