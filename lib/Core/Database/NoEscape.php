<?php
/**
 * @TODO find another, more general way of doing this
 */
class NoEscape {
  $string = '';

  public function __construct($string) {
    $this->string = $string;
  }

  public function __toString() {
    return $this->string;
  }
}
