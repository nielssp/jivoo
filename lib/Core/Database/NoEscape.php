<?php
/**
 * @TODO find another, more general way of doing this
 * @TODO Maybe new Expression() or new QueryExpression() etc. instead
 */
class NoEscape {
  private $string = '';

  public function __construct($string) {
    $this->string = $string;
  }

  public function __toString() {
    return $this->string;
  }
}
