<?php
/**
 * A controller, the C of MVC
 * 
 * @todo docs
 * @package Core
 * @subpackage Controllers
 */
class Controller implements IHelpable {

  private $name;

  protected $config = null;
  protected $auth = null;
  protected $m = null;
  protected $request = null;
  protected $session = null;

  private $actions = array();

  private $templatePaths = array();

  private $data = array();

  protected $modules = array();
  protected $helpers = array('Html');
  protected $models = array();
  private $helperObjects = array();
  private $modelObjects = array();

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

  public function __get($name) {
    if (isset($this->modelObjects[$name])) {
      return $this->modelObjects[$name];
    }
    if (isset($this->helperObjects[$name])) {
      return $this->helperObjects[$name];
    }
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }
  
  public function setConfig(AppConfig $config) {
    $this->config = $config;
  }

  public function getData() {
    return $this->data;
  }
  
  public function getModuleList() {
    return $this->modules;
  }
  
  public function getHelperList() {
    return $this->helpers;
  }
  
  public function getModelList() {
    return $this->models;
  }

  public function addModule($object) {
    $class = get_class($object);
    if ($object instanceof Authentication) {
      $this->auth = $object;
    }
    $this->m->$class = $object;
  }
  
  public function addHelper($helper) {
    $name = str_replace('Helper', '', get_class($helper));
    $this->helperObjects[$name] = $helper;
  }
  
  public function addModel($name, IModel $model) {
    $this->modelObjects[$name] = $model;
  }

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
    $class = get_class($this);
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
    $paction = Utilities::camelCaseToDashes($action);
    if ($action == 'index') {
      $this->addRoute($controller, $action);
    }
    $path = $controller . '/' . $paction;
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

  public function autoRoute($action = null, $prefix = '') {
    if (isset($action)) {
      $this->createRoute($action, $prefix);
      return;
    }
    foreach ($this->actions as $action) {
      $this->createRoute($action, $prefix);
    }
  }

  public function addRoute($path, $action, $priority = 5) {
    $this->m->Routing->addRoute(
      $path,
      array('controller' => $this->name, 'action' => $action),
      $priority
    );
  }

  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routing->setRoute(
      array(
        'controller' => $this->name,
        'action' => $action,
        'parameters' => $parameters
      ), $priority
    );
  }

  protected function reroute() {
    list(, $caller) = debug_backtrace(false);
    $this->m->Routing
      ->reroute($this->name, $caller['function'], $caller['args']);
  }

  protected function returnToThis() {
    //list( , $caller) = debug_backtrace(false);
    //    $this->session['returnTo'] = array('url' => $this->request->url);
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
  }

  protected function goBack() {
    if (!isset($this->session['returnTo'])) {
      return false;
    }
    $this->redirect($this->session['returnTo']);
  }

  protected function redirect($route = null) {
    $this->m->Routing->redirect($route);
  }

  protected function refresh($query = null, $fragment = null) {
    $this->m->Routing->refresh($query, $fragment);
  }

  public function addTemplatePath($path) {
    $this->templatePaths[] = $path;
  }

  protected function render($templateName = null, $return = false) {
    $template = new Template($this->m->Templates, $this->m->Routing, $this);
    $template->setTemplatePaths($this->templatePaths);
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
    $templateData = array_merge($this->data, $this->helperObjects);
    $template->set($templateData);
    return $template->render($templateName, $return);
  }

  protected function init() {
  }
  
  public function preRender() {
  }

}
