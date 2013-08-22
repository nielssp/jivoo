<?php
/**
 * A value that should not be escaped in database queries
 * @package Core\Database
 * @TODO find another, more general way of doing this
 * @TODO Maybe new Expression() or new QueryExpression() etc. instead
 */
class NoEscape {
  /**
   * @var string String
   */
  private $string = '';

  /**
   * Constructor.
   * @param string $string String
   */
  public function __construct($string) {
    $this->string = $string;
  }

  /**
   * Convert to string
   * @return string String
   */
  public function __toString() {
    return $this->string;
  }
}
