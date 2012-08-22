<?php

abstract class ApplicationHelper {

  
  protected $m = null;
  protected $request = null;
  protected $session = null;
  protected $auth = null;

  protected $controller = null;

  protected $helpers = array();

  private $helperObjects = array();

  public final function __construct(Routes $routes, $controller = null) {
    $this->m = new Dictionary();
    
    $routes->addHelper($this);
    
    $this->request = $routes->getRequest();
    $this->session = $this->request->session;

    $this->controller = $controller;
    
    foreach ($this->helpers as $name) {
      $class = $name . 'Helper';
      if (class_exists($class)) {
        $this->helperObjects[$name] = new $class($routes, $this);
      }
    }

    $this->init();
  }

  public function __get($name) {
    if (isset($this->helperObjects[$name])) {
      return $this->helperObjects[$name];
    }
  }

  protected function init() {
  }
  
  public function addModule($object) {
    $class = get_class($object);
    if ($object instanceof Authentication) {
      $this->auth = $object;
    }
    $this->m->$class = $object;
  }
  
  protected function getLink($route) {
    return $this->m->Routes->getLink($route);
  }

}
