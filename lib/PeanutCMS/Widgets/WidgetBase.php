<?php
/**
 * Widget base class
 * @package PeanutCMS\Widgets
 */
abstract class WidgetBase implements IHelpable {
  /**
   * @var WidgetView Widget view
   */
  protected $view;
  
  /**
   * @var Dictionary Collection of modules
   */
  private $m = null;

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
  public function __construct(Routing $routing, $defaultTemplate) {
    $this->view = new WidgetView($routing, $defaultTemplate);
    $this->m = new Dictionary();
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
    $this->helperObjects[$name] = $helper;
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
   * Main widget logic. Is called before rendering page with widget on.
   * @param array $config Associative array of widget configuration
   * @return string|false Widget HTML or false on error
   */
  public abstract function main($config);
}