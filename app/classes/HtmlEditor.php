<?php

class HtmlEditor implements IEditor {
  protected $format = NULL;
  
  public function __construct() {
    $this->format = new HtmlFormat();
  }

  public function init() {
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
