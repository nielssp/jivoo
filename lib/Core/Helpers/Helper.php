<?php
/**
 * A helper for use in controllers and views
 * @package Core\Helpers
 */
abstract class Helper implements IHelpable {
  /**
   * @var Dictionary Collection of modules
   */
  protected $m = null;
  
  /**
   * @var Request Current request
   */
  protected $request = null;
  
  /**
   * @var Session Current session
   */
  protected $session = null;

  /**
   * @var string[] A list of modules needed by this helper
   */
  protected $modules = array();
  
  /**
   * @var string[] A list of other helpers needed by this helper
   */
  protected $helpers = array();
  
  /**
   * @var string[] A list of models needed by this helper
   */
  protected $models = array();

  /**
   * @var array An associative array of helper names and objects
   */
  private $helperObjects = array();

  /**
   * @var array An associative array of model names and objects
   */
  private $modelObjects = array();

  /**
   * Constructor.
   * @param Routing $routing Routing module
   */
  public final function __construct(Routing $routing) {
    $this->m = new Dictionary();
    
    $this->m->Routing = $routing;

    $this->request = $routing->getRequest();
    $this->session = $this->request->session;

    $this->init();
  }

  /**
   * Get a model instance or a helper instance, in that order.
   * @param string $name Name of model or helper (without 'Helper'-suffix)
   * @return Model|Helper|void Model object or helper object
   */
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    if (isset($this->helperObjects[$name])) {
      return $this->helperObjects[$name];
    }
  }

  /**
   * Initialisation method called by constructor.
   */
  protected function init() {}

  /**
   * Get list of modules requested by this helper 
   * @return string[] List of module names
   */
  public function getModuleList() {
    return $this->modules;
  }
  
  /**
   * Get list of models requested by this helper
   * @return string[] List of model names
   */
  public function getModelList() {
    return $this->models;
  }

  /**
   * Add a module
   * @param ModuleBase $object Module object
   */
  public function addModule($object) {
    $class = get_class($object);
    $this->m->$class = $object;
  }
  
  /**
   * Add a model object
   * @param string $name Model name
   * @param IModel $model Model object
   */
  public function addModel($name, IModel $model) {
    $this->modelObjects[$name] = $model;
  }
  
  /**
   * Convert a route to a link
   * @param array|ILinkable|string|null $route Route, see {@see Routing}
   * @return string A link
   */
  protected function getLink($route) {
    return $this->m->Routing->getLink($route);
  }
  
  /* IHelpable implementation: */
  
  public function getHelperList() {
    return $this->helpers;
  }
  
  public function addHelper($helper) {
    $name = str_replace('Helper', '', get_class($helper));
    $this->helperObjects[$name] = $helper;
  }

}
