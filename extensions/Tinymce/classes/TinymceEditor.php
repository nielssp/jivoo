<?php

class TinymceEditor implements IEditor {

  private $format = NULL;
  private $tinymce = NULL;

  public function __construct(Tinymce $tinymce) {
    $this->format = new HtmlFormat();
    $this->tinymce = $tinymce;
  }

  public function init() {
    $this->tinymce->insertScripts();
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    $options['class'] = 'tinymce';
    return $Form->textarea($field, $options);
  }
}
