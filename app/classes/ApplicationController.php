<?php

class ApplicationController {
  
  private $name;

  protected $config = NULL;
  protected $auth = NULL;
  protected $m = NULL;
  protected $e = NULL;
  protected $request = NULL;
  protected $session = NULL;
  
  private $actions = array();
  
  private $templatePaths = array();
  
  private $data = array();

  protected $helpers = array('Html');
  private $helperObjects = array();
  
  
  public final function __construct(Routes $routes, Configuration $config = NULL) {
    $this->m = new Dictionary();
    $this->e = new Dictionary();

    $routes->addController($this);
    
    $this->config = $config;
    
    $this->request = $routes->getRequest();
    $this->session = $this->request->session;
    
    $this->name = substr(get_class($this), 0, -10);
    
    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);

    foreach ($this->helpers as $name) {
      $class = $name . 'Helper';
      if (class_exists($class)) {
        $this->helperObjects[$name] = new $class($routes, $this);
      }
    }
    
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

  public function addModule($object) {
    $class = get_class($object);
    if ($object instanceof Authentication) {
      $this->auth = $object;
    }
    $this->m->$class = $object;
  }

  public function addExtension($object) {
    $class = get_class($object);
    $this->e->$class = $object;
  }
  
  private function createRoute($action, $prefix = '') {
    $reflect = new ReflectionMethod(get_class($this), $action);
    $required = $reflect->getNumberOfRequiredParameters();
    $total = $reflect->getNumberOfParameters();
    if (!empty($prefix) AND substr($prefix, -1) != '/') {
      $prefix .= '/';
    }
    $controller = $prefix . classFileName($this->name);
    $paction = classFileName($action);
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
  
  public function autoRoute($action = NULL, $prefix = '') {
    if (isset($action)) {
      $this->createRoute($action, $prefix);
      return;
    }
    foreach ($this->actions as $action) {
      $this->createRoute($action, $prefix);
    }
  }
  
  public function addRoute($path, $action, $priority = NULL) {
    $this->m->Routes->addRoute($path, $this, $action, $priority);
  }

  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routes->setRoute($this, $action, $priority, $parameters);
  }

  protected function reroute() {
    list( , $caller) = debug_backtrace(false);
    $this->m->Routes->reroute($this->name, $caller['function'], $caller['args']);
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
      return FALSE;
    }
    $this->redirect($this->session['returnTo']);
  }
  
  protected function redirect($route = NULL) {
    $this->m->Routes->redirect($route);
  }
  
  protected function refresh($query = NULL, $fragment = NULL) {
    $this->m->Routes->refresh($query, $fragment);
  }

  public function addTemplatePath($path) {
    $this->templatePaths[] = $path;
  }
  
  protected function render($templateName = NULL) {
    $template = new Template($this->m->Templates, $this->m->Routes, $this);
    $template->setTemplatePaths($this->templatePaths);
    if (!isset($templateName)) {
      $templateName = classFileName($this->name) . '/';
      list( , $caller) = debug_backtrace(false);
      $templateName .= classFileName($caller['function']) . '.html';
    }
    $templateData = array_merge($this->data, $this->helperObjects);
    $template->set($templateData);
    $template->render($templateName);
  }

  public function init() {
  }

  public function notFound() {
    $this->render('404.html');
  }
  
}
