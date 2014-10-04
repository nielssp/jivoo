<?php

class TinymceEditor implements IEditor {

  private $tinymce = null;

  public function __construct(Tinymce $tinymce) {
    $this->tinymce = $tinymce;
  }

  public function getFormat() {
    return 'html';
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    $this->tinymce->insertScripts();
    $options['class'] = 'tinymce';
    return $Form->textarea($field, $options);
  }
}
