<?php
/**
 * Widget base class
 * @package PeanutCMS\Widgets
 */
abstract class Widget extends Module {
  protected $modules = array('Helpers', 'Models');
  /**
   * @var string[] A list of other helpers needed by this helper
   */
  protected $helpers = array();
  
  /**
   * @var string[] A list of models needed by this helper
   */
  protected $models = array();

  /**
   * @var array An associative array of model names and objects
   */
  private $modelObjects = array();
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
   * Constructor
   * @param Routing $routing Routing module
   * @param string $defaultTemplate Absolute path to default widget template
   */
  public function __construct(App $app, $defaultTemplate) {
    $this->inheritElements('modules');
    $this->inheritElements('helpers');
    $this->inheritElements('models');
    parent::__construct($app);
    $this->modelObjects = $this->m->Models->getModels($this->models);
    $helperObjects = $this->m->Helpers->getHelpers($this->helpers);
    foreach ($helperObjects as $name => $helper)
      $this->$name = $helper;
    $this->template = $defaultTemplate;
  }
  
  /**
   * Get value of data variable
   * @param string $name Variable name
   * @return mixed Value
  */
  public function __get($name) {
    if (isset($this->modelObjects[$name]))
      return $this->modelObjects[$name];
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
   * Convert a route to a URL
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @return string URl
   */
  protected function url($route = null) {
    return $this->m->Routing->getUrl($route);
  }
  
  /**
   * Convert a route to a URL
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @return string URl
   */
  public function link($route = null) {
    return $this->m->Routing->getLink($route);
  }

  /**
   * Get a link to an asset
   * @param string $file Asset
   * @return string Absolute path to asset
   */
  protected function file($file) {
    return $this->m->Templates->getAsset($file);
  }
  
  /**
   * Check whether or not a route is the current route
   * @param array|ILinkable|string|null $route A route, see {@see Routing}
   * @return bool True if current, fals otherwise
   */
  public function isCurrent($route = null) {
    return $this->m->Routing->isCurrent($route);
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
   * Fetch template for widget
   * @return string Widget HTML
   */
  public function fetch() {
    extract($this->data);
    ob_start();
    require $this->template;
    return ob_get_clean();
  }
  
  /**
   * Main widget logic. Is called before rendering page with widget on.
   * @param array $config Associative array of widget configuration
   * @return string|false Widget HTML or false on error
   */
  public abstract function main($config);
}