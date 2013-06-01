<?php

class TextEditor extends HtmlEditor {
  public function __construct() {
    $this->format = new TextFormat();
  }
}
