<?php

class TinymceEditor implements IEditor {

  private $format = NULL;
  private $tinymce = NULL;
  private $config = NULL;
  private $initiated = FALSE;

  public function __construct(Tinymce $tinymce) {
    $this->format = new HtmlFormat();
    $this->tinymce = $tinymce;
  }

  public function init(Configuration $config = NULL) {
    $this->tinymce->insertScripts();
    $this->config = $config;
    if ($this->initiated) {
      $class = get_class($this);
      $instance = new $class($this->tinymce);
      return $instance->init();
    }
    $this->initiated = TRUE;
    return $this;
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    $options['class'] = 'tinymce';
    return $Form->textarea($field, $options);
  }
}
