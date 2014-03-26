<?php
/**
 * Widget base class
 * @package PeanutCMS\Widgets
 */
abstract class WidgetBase implements IHelpable {
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
   * @var Dictionary Collection of modules
   */
  protected $m = null;

  /**
   * @var string[] List of helpers needed by controller
   */
  protected $helpers = array('Html');

  /**
   * @var string[] List of models needed by controller
   */
  protected $models = array();
  
  /**
   * @var array Associative array of model names and {@see Model} objects
   */
  private $modelObjects = array();
  
  /**
   * @var Request Current request
   */
  protected $request = null;
  
  /**
   * @var Session Current session
   */
  protected $session = null;
  
  /**
   * Constructor
   * @param Routing $routing Routing module
   * @param string $defaultTemplate Absolute path to default widget template
   */
  public function __construct(Templates $templates, Routing $routing, $defaultTemplate) {
    $this->template = $defaultTemplate;
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routing = $routing;
    $this->request = $routing->getRequest(); 
    $this->session = $this->request->session;
  }
  
  /**
   * Get value of data variable
   * @param string $name Variable name
   * @return mixed Value
  */
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
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
  
  public function getHelperList() {
    return $this->helpers;
  }
  
  /**
   * Get list of models that controller requires
   * @return string[] List of model names
   */
  public function getModelList() {
    return $this->models;
  }
  
  /**
   * Add a module to the {@see Controller::$m} dictionary
   * @param ModuleBase $object Module
   */
  public function addModule($object) {
    $class = get_class($object);
    $this->m->$class = $object;
  }
  
  public function addHelper($helper) {
    $name = str_replace('Helper', '', get_class($helper));
    $this->$name = $helper;
  }
  
  /**
   * Add a model to this controller
   * @param string $name Name of model
   * @param IModel $model Model object
   */
  public function addModel($name, IModel $model) {
    $this->modelObjects[$name] = $model;
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