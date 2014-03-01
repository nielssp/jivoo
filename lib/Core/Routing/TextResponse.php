<?php
class TextResponse extends Response {
  private $text;

  public function __construct($status, $type, $text) {
    parent::__construct($status, $type);
    $this->text = $text;
  }

  public function render() {
    echo $this->text;
  }
}
