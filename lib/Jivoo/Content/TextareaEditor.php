<?php
class TextareaEditor implements IEditor {

  private $format;
  
  public function __construct($format) {
    $this->format = $format;
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
