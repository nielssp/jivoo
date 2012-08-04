<?php

class HtmlEditor implements IEditor {
  
  private $format = NULL;
  
  public function __construct() {
    $this->format = new HtmlFormat();
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }

}
