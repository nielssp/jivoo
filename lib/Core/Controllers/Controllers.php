<?php
// Module
// Name           : Controllers
// Version        : 0.3.14
// Description    : For contollers
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates
//                  Core/Helpers

/**
 * Controllers module
 * 
 * @package Core
 * @subpackage Controllers
 */
class Controllers extends ModuleBase {
  
  private $controllers = array();
  
  protected function init() {

    Lib::addIncludePath($this->app->paths->controllers);
    
    $dir = opendir($this->app->paths->controllers);
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
        $class = $split[0];
        if (class_exists($class) && is_subclass_of($class, 'Controller')) {
          $this->addController(new $class($this->m->Routing, $this->app->config));
        }
      }
    }

    $controller = new Controller($this->m->Routing, $this->config);
    $controller->setRoute('notFound', 1);
  }
  
  public function addController(Controller $controller) {
    $name = str_replace('Controller', '', get_class($controller));
    $this->controllers[$name] = $controller;
  }
  
  public function getController($name) {
    return $this->controllers[$name];
  }
  
  public function __get($name) {
    return $this->getController($name);
  }
}