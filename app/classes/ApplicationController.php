<?php

class ApplicationController {
  
  private $name;

  protected $m = NULL;
  private $Templates = NULL;
  private $Routes = NULL;
  private $actions = array();
  
  private $data = array();

  protected $helpers = array();
  private $helperObjects = array();
  
  public final function __construct(Templates $templates, Routes $routes) {
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routes = $routes;
    
    $this->name = str_replace('-controller', '', classFileName(get_class($this)));
    
    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);

    foreach ($this->helpers as $helper) {
      $name = className($helper);
      $class = $name . 'Helper';
      if (class_exists($class)) {
        $this->helperObjects[$name] = new $class($templates, $routes, $this->name);
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
  
  public function autoRoute() {
    foreach ($this->actions as $action) {
      $reflect = new ReflectionMethod(get_class($this), 'view');
      $required = $reflect->getNumberOfRequiredParameters();
      $total = $reflect->getNumberOfParameters();
      $action = classFileName($action);
      if ($action == 'index') {
        $this->addRoute($this->name, $action);
      }
      $path = $this->name . '/' . $action;
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
  }
  
  public function addRoute($path, $action, $priority = NULL) {
    $this->m->Routes->addRoute($path, array($this, $action), $priority);
  }

  public function setRoute($action, $priority = 5, $parameters = array()) {
    $this->m->Routes->setRoute(array($this, $action), $priority, $parameters);
  }
  
  protected function redirect() {
    
  }
  
  protected function refresh() {
    
  }
  
  protected function render($template = NULL) {
    //$template = new Template();
    if (!isset($template)) {
      $template= $this->name . '/';
      list( , $caller) = debug_backtrace(false);
      $template .= $caller['function'] . '.html';
    }
    $templateData = array_merge($this->data, $this->helperObjects);
    $this->m->Templates->renderTemplate($template, $templateData);
  }

  public function notFound() {
    $this->render('404.html');
  }
  
}
