<?php
/**
 * An editor for editing HTMl
 * @package Core\Editors
 */
class HtmlEditor implements IEditor {
  /**
   * @var IContentFormat Content format
   */
  protected $format = null;
  
  /**
   * @var AppConfig Configuration
   */
  protected $config = null;
  
  /**
   * @var bool Whether or not editor has been initialised
   */
  protected $initiated = false;

  /**
   * Constructor
   */
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
