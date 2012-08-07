<?php

class HtmlEditor implements IEditor {
  protected $format = NULL;
  protected $config = NULL;
  protected $initiated = FALSE;
  
  public function __construct() {
    $this->format = new HtmlFormat();
  }

  public function init(Configuration $config = NULL) {
    $this->config = $config;
    if ($this->initiated) {
      $class = get_class($this);
      $instance = new $class();
      return $instance->init();
    }
    $this->initiated = TRUE;
    return $this;
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
