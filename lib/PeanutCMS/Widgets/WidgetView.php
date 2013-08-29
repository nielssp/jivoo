<?php
/**
 * Widget view/template
 * @package PeanutCMS\Widgets
 */
class WidgetView {
  /**
   * @var array Associative array of widget data
   */
  private $data = array();
  
  /**
   * @var string Absolute path to template
   */
  private $template;
  
  /**
   * @var bool Whether or not template is default
   */
  private $default = true;
  
  /**
   * Constructor.
   * @param string $defaultTemplate Absolute path to default widget template
   */
  public function __construct($defaultTemplate) {
    $this->template = $defaultTemplate;
  }
  
  /**
   * Get value of data variable
   * @param string $name Variable name
   * @return mixed Value
  */
  public function __get($name) {
    return $this->data[$name];
  }

  /**
   * Set value of data variable
   * @param string $name Variable name
   * @param mixed $value Value
   */
  public function __set($name, $value) {
    $this->data[$name] = $value;
  }

  /**
   * Check whether or not a variable is set
   * @param string $name Variable name
   */
  public function __isset($name) {
    return isset($this->data[$name]);
  }
  
  /**
   * Set alternate widget template
   * @param string $template Absolute path to template
   */
  public function setTemplate($template) {
    $this->default = false;
    $this->template = $template;
  }
  
  /**
   * Whether or not the current template is the default
   * @return boolean True if default template, false otherwise
   */
  public function isDefaultTemplate() {
    return $this->default;
  }
  
  /**
   * Fetch template for widget
   * @return string Widget HTML
   */
  public function fetch() {
    extract($this->data);
    ob_start();
    require $this->template;
    return ob_get_clean();
  }
}