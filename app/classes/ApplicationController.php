<?php

class ApplicationController {
  
  private $name;

  protected $m = NULL;
  protected $e = NULL;
  protected $request = NULL;
  
  private $actions = array();
  
  private $templatePaths = array();
  
  private $data = array();

  protected $helpers = array('Html');
  private $helperObjects = array();
  
  
  public final function __construct(Templates $templates, Routes $routes) {
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routes = $routes;

    $this->e = new Dictionary();
    
    $this->request = $routes->getRequest();
    
    $this->name = substr(get_class($this), 0, -10);
    
    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);

    foreach ($this->helpers as $helper) {
      $name = className($helper);
      $class = $name . 'Helper';
      if (class_exists($class)) {
        $this->helperObjects[$name] = new $class($templates, $routes, $this);
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
    $action = classFileName($action);
    if ($action == 'index') {
      $this->addRoute($controller, $action);
    }
    $path = $controller . '/' . $action;
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
    $this->m->Routes->addRoute($path, array($this, $action), $priority);
  }

  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routes->setRoute(array($this, $action), $priority, $parameters);
  }

  protected function reroute() {
    list( , $caller) = debug_backtrace(false);
    $this->m->Routes->reroute($this->name, $caller['function'], $caller['args']);
  }
  
  protected function redirect($route = NULL) {
    $this->m->Routes->redirect($route);
  }
  
  protected function refresh() {
    $this->m->Routes->refresh();
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
      $templateName .= $caller['function'] . '.html';
    }
    $templateData = array_merge($this->data, $this->helperObjects);
    $template->set($templateData);
    $template->render($templateName);
  }

  public function notFound() {
    $this->render('404.html');
  }
  
}
