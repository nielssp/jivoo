<?php

class HtmlEditor implements IEditor {
  protected $format = null;
  protected $config = null;
  protected $initiated = false;

  public function __construct() {
    $this->format = new HtmlFormat();
  }

  public function init(AppConfig $config = null) {
    $this->config = $config;
    if ($this->initiated) {
      $class = get_class($this);
      $instance = new $class();
      return $instance->init();
    }
    $this->initiated = true;
    return $this;
  }

  public function getFormat() {
    return $this->format;
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $Form->textarea($field, $options);
  }
}
