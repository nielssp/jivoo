<?php

abstract class ApplicationController {
  
  private $name;

  private $Core = NULL;
  private $Routes = NULL;
  private $actions = array();
  
  private $data = array();
  
  public final function __construct(Core $core) {
    $this->Core = $core;
    $this->Routes = $core->requestModule('Routes');
    
    $this->name = str_replace('-controller', '', classFileName(get_class($this)));
    
    $classMethods = get_class_methods($this);
    $parentMethods = get_class_methods(__CLASS__);
    $this->actions = array_diff($classMethods, $parentMethods);
  }
  
  public function __get($name) {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
  }
  
  public function __set($name, $value) {
    $this->data[$name] = $value;
  }
  
  public function autoRoute() {
    foreach ($this->actions as $action) {
      $path = $this->name . '/' . classFileName($action) . '/**';
      $this->addRoute($path, $action);
    }
  }
  
  public function addRoute($path, $action) {
    $this->Routes->addRoute($path, array($this, $action));
  }
  
  protected function render($template) {
    //$template = new Template();
    $this->Core->Templates->renderTemplate($template, $this->data);
  }
  
}