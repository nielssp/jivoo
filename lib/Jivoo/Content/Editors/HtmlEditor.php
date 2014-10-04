<?php
/**
 * Like {@see HtmlEditor}, but its content format, {@see TextFormat}, will
 * automatically convert line breaks and links.
 * @package Jivoo\Editors
 */
class HtmlEditor implements IEditor {
  
  protected $format = 'html';
  
  public function getFormat() {
    return $this->format;
  }
  
  public function saveFilter($content) {
    return $content;
  }
  
  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
