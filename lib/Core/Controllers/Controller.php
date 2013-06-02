<?php
/**
 * A controller, the C of MVC
 * 
 * @todo docs
 * @package Core
 * @subpackage Controllers
 */
class Controller {

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
  private $helperObjects = array();

  public final function __construct(Routing $routing, AppConfig $config = null) {
    $this->m = new Dictionary();

    $this->m->Routing = $routing;

    $this->config = $config;

    $this->request = $routing->getRequest(); 
    $this->session = $this->request->session;

    $this->name = str_replace('Controller', '', get_class($this));

    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);

    $this->init();
  }

  public function __get($name) {
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

  public function addModule($object) {
    $class = get_class($object);
    if ($object instanceof Authentication) {
      $this->auth = $object;
    }
    $this->m->$class = $object;
  }
  
  public function addHelper($helper) {
    $name = str_replace('Helper', '', get_class($object));
    $this->helperObjects[$name] = $helper;
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
    $controller = $prefix . Utilities::camelCaseToDashes($this->name);
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

  public function addRoute($path, $action, $priority = null) {
    $this->m->Routing->addRoute($path, $this, $action, $priority);
  }

  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routing->setRoute($this, $action, $priority, $parameters);
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
      $templateName = Utilities::camelCaseToDashes($this->name) . '/';
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

  public function notFound() {
    $this->render('404.html');
  }

}
