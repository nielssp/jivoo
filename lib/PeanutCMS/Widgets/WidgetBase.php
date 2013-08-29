<?php
/**
 * Widget base class
 * @package PeanutCMS\Widgets
 */
abstract class WidgetBase {
  /**
   * @var WidgetView Widget view
   */
  protected $view;
  
  /**
   * Constructor
   * @param string $defaultTemplate Absolute path to default widget template
   */
  public function __construct($defaultTemplate) {
    $this->view = new WidgetView($defaultTemplate);
  }
  
  /**
   * Get value of data variable
   * @param string $name Variable name
   * @return mixed Value
   */
  public function __get($name) {
    return $this->view->$name;
  }
  
  /**
   * Set value of data variable
   * @param string $name Variable name
   * @param mixed $value Value
   */
  public function __set($name, $value) {
    $this->view->$name = $value;
  }
  
  /**
   * Get widget view
   * @return WidgetView Widget view
   */
  public function getView() {
    return $this->view;
  }
  
  /**
   * Default title for widget
   * @return string Title
   */
  public function getDefaultTitle() {
    return '';
  }
  
  /**
   * Main widget logic. Is called before rendering page with widget on.
   * @param array $config Associative array of widget configuration
   * @return string|false Widget HTML or false on error
   */
  public abstract function main($config);
}