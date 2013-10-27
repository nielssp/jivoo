<?php
/**
 * A controller, the C of MVC
 * @package Core\Controllers
 */
class Controller implements IHelpable {
  /**
   * @var string Name of controller (without 'Controller'-part)
   */
  private $name;

  /**
   * @var AppConfig Configuration
   */
  protected $config = null;
  
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
   * @var string[] List of actions
   */
  private $actions = array();

  /**
   * @var string[] List of template paths
   */
  private $templatePaths = array();

  /**
   * @var array Associative array of data to be passed along to template
   */
  private $data = array();

  /**
   * @var string[] List of modules needed by controller
   */
  protected $modules = array();

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
   * @var View Current view
   */
  protected $view = null;
  
  /**
   * @var HTTP status code
   */
  private $status = 200;
  
  /**
   * Constructor
   * @param Routing $routing Routing module
   * @param Templates $templates Templates module
   * @param AppConfig $config Configuration
   * @param bool $temp Set to true if temporary (used to get list of helpers,
   * modules and models without spending time initialising)
   */
  public final function __construct(Routing $routing, Templates $templates,
                                    AppConfig $config = null, $temp = false) {
    if ($temp) {
      return;
    }
    $this->m = new Dictionary();

    $this->m->Routing = $routing;
    $this->m->Templates = $templates;

    $this->config = $config;

    $this->request = $routing->getRequest(); 
    $this->session = $this->request->session;
    
    $this->view = $templates->view;

    $this->name = str_replace('Controller', '', get_class($this));

    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);
    
    $class = get_parent_class($this);
    while ($class !== false AND $class != 'Controller') {
      $temp = new $class($routing, $templates, null, true);
      $this->helpers = array_unique(
        array_merge($this->helpers, $temp->helpers)
      );
      $this->models = array_unique(
        array_merge($this->models, $temp->models)
      );
      $this->modules = array_unique(
        array_merge($this->modules, $temp->modules)
      );
      $class = get_parent_class($class);
    }
    $this->init();
  }

  /**
   * Get an associated model, helper or data-value (in that order)
   * @param string $name Name of model/helper or key for data-value
   * @return Model|Helper|mixed Associated value 
   */
  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    return $this->view->$name;
  }

  /**
   * Set data value, the data is passed along to the template when rendering
   * @param string $name Key
   * @param mixed $value Value
   */
  public function __set($name, $value) {
    $this->view->$name = $value;
  }
  
  /**
   * Set configuration
   * @param AppConfig $config Configuration
   */
  public function setConfig(AppConfig $config) {
    $this->config = $config;
  }
  
  /**
   * Get list of modules that this controller requires
   * @return string[] List of module names
   */
  public function getModuleList() {
    return $this->modules;
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
   * Create a route to an action for auto routing
   * @param string $action Action name
   * @param string $prefix Prefix for the resulting path
   * @throws Exception If action does not exist
   */
  private function createRoute($action, $prefix = '') {
    if (!in_array($action, $this->actions)) {
      throw new Exception(tr('Invalid action "%1"', $action));
    }
    $reflect = new ReflectionMethod(get_class($this), $action);
    $required = $reflect->getNumberOfRequiredParameters();
    $total = $reflect->getNumberOfParameters();
    if (!empty($prefix) AND substr($prefix, -1) != '/') {
      $prefix .= '/';
    }
    $controller = '';
    $paction = '';
    $class = get_class($this);
    if ($class != 'AppController') {
      $parent = get_parent_class($class);
      while ($parent !== false AND $parent != 'Controller'
        AND $parent != 'AppController') {
        $name = str_replace($parent, '', $class);
        $controller = '/' . Utilities::camelCaseToDashes($name) . $controller;
        $class = $parent;
        $parent = get_parent_class($class);
      }
      $name = str_replace('Controller', '', $class);
      $controller = $prefix . Utilities::camelCaseToDashes($name) . $controller;
      $paction = '/';
    }
    
    $paction .= Utilities::camelCaseToDashes($action);
    if ($action == 'index') {
      $this->addRoute($controller, $action);
    }
    $path = $controller . $paction;
    if ($required < 1) {
      $this->addRoute($path, $action);
    }
    for ($i = 0; $i < $total; $i++) {
      $path .= '/*';
      if ($i <= $required) {
        $this->addRoute($path, $action);
      }
    }
  }

  /**
   * Automatically route a single or all actions in this controller
   * @param string $action If set, the name of the single action to auto route
   * @param string $prefix A prefix to use for all resulting paths
   */
  public function autoRoute($action = null, $prefix = '') {
    $this->m->Routing->autoRoute($this->name, $action, $prefix);
  }

  /**
   * Add a custom route to an action
   * @param string $path Path that should lead to action
   * @param string $action Action name
   * @param int $priority Priority of route
   */
  public function addRoute($path, $action, $priority = 5) {
    $this->m->Routing->addRoute(
      $path,
      array('controller' => $this->name, 'action' => $action),
      $priority
    );
  }

  /**
   * Set the current route
   * @param stirng $action Action name
   * @param int $priority Priority of route
   * @param mixed[] $parameters Array of additional parameters for action
   */
  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routing->setRoute(
      array(
        'controller' => $this->name,
        'action' => $action,
        'parameters' => $parameters
      ), $priority
    );
  }

  /**
   * Ensure that the current path matches the current action, redirect to
   * the correct path if not.
   */
  protected function reroute() {
    list(, $caller) = debug_backtrace(false);
    $this->m->Routing
      ->reroute($this->name, $caller['function'], $caller['args']);
  }

  /**
   * Set return path
   * @see Controller::goBack()
   */
  protected function returnToThis() {
    //list( , $caller) = debug_backtrace(false);
    //    $this->session['returnTo'] = array('url' => $this->request->url);
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
  }

  /**
   * Return to a previously set return path
   * @see Controller::returnToThis()
   * @return false False if no return path set
   */
  protected function goBack() {
    if (!isset($this->session['returnTo'])) {
      return false;
    }
    unset($this->session['returnTo']);
    $this->redirect($this->session['returnTo']);
  }

  /**
   * Redirect to a route
   * @param array|ILinkable|string|null $route Route, see {@see Routing}
   */
  protected function redirect($route = null) {
    $this->m->Routing->redirect($route);
  }

  /**
   * Refresh the current path with optional query data and fragment
   * @param array $query Associative array of query data
   * @param string $fragment Fragment of page
   */
  protected function refresh($query = null, $fragment = null) {
    $this->m->Routing->refresh($query, $fragment);
  }

  /**
   * Set HTTP status code, e.g. 200 for OK or 404 for file not found.
   * @param integer $httpStatus HTTP status code
   */
  protected function setStatus($httpStatus) {
    $this->status = $httpStatus;
  }
  
  /**
   * Render a template
   * 
   * If $templateName is not set, the path of the template will be computed
   * based on the name of the controller and the name of the action. Each level
   * of inheritance will be an additional directory level. The AppController
   * does not count, and will result in the root of the template directory. An
   * example is {@see AuthenticationSetupController} which inherits from
   * {@see SetupController}. If the action is 'setupRoot', the resulting
   * template path is 'setup/authentication/setup-root.html'.
   * {@see Utilities::camelCaseToDashes()} is used on each level.
   * 
   * @param string $templateName Name of template to render
   * @return string The output of $return is set to true
   */
  protected function render($templateName = null) {
    if (!isset($templateName)) {
      $templateName = '';
      $thisName = $this->name;
      if ($thisName != 'App') {
        $class = get_class($this);
        $parent = get_parent_class($class);
        while ($parent !== false AND $parent != 'Controller'
          AND $parent != 'AppController') {
          $name = str_replace($parent, '', $class);
          $templateName = Utilities::camelCaseToDashes($name) . '/' . $templateName;
          $class = $parent;
          $parent = get_parent_class($class);
        }
        $name = str_replace('Controller', '', $class);
        $templateName = Utilities::camelCaseToDashes($name) . '/' . $templateName;
      }
      list(, $caller) = debug_backtrace(false);
      $templateName .= Utilities::camelCaseToDashes($caller['function'])
        . '.html';
    }
    Http::setStatus($this->status);
    $this->view->display($templateName);
  }

  /**
   * Controller initialisation, called by constructor. Modules, helpers and
   * models are NOT available when this function is called.
   */
  protected function init() {
  }
  
  /**
   * Called just before the selected action is called, useful for doing stuff
   * for all actions.
   */
  public function preRender() {
  }

}
