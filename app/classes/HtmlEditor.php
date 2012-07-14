<?php

class HtmlEditor implements IEditor {
  
  private $format = NULL;
  private $encoder = NULL;
  
  public function __construct() {
    $this->format = new HtmlFormat();
  }

  public function init() {
  }

  public function setEncoder(Encoder $encoder) {
    $this->encoder = $encoder;
  }
  
  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }

}
