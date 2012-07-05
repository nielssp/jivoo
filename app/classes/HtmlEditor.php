<?php

class HtmlEditor implements IEditor {
  
  private $format = NULL;
  
  public function __construct() {
    $this->format = new HtmlFormat();
  }
  
  public function configure(Configuration $config) {
    $this->config = $config;
    $this->format->configure($config->getSubset('format'));
  }
  
  public function getFormat() {
    return $this->format;
  }

}