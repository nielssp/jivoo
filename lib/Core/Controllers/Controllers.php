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

  private $controllerObjects = array();

  protected function init() {

    Lib::addIncludePath($this->app->paths->controllers);

    $dir = opendir($this->app->paths->controllers);
    while ($file = readdir($dir)) {
      $split = explode('.', $file);
      if (isset($split[1]) AND $split[1] == 'php') {
        $class = $split[0];
        if (class_exists($class) && is_subclass_of($class, 'Controller')) {
          $name = str_replace('Controller', '', $class);
          $this->controllers[$name] = $class;
        }
      }
    }
  }

  private function getInstance($name) {
    if (isset($this->controllers[$name])) {
      if (!isset($this->controllerObjects[$name])) {
        $class = $this->controllers[$name];
        $this->controllerObjects[$name] = new $class($this->m
            ->Routing, $this->m
            ->Templates, $this->app
            ->config);
        $controller = $this->controllerObjects[$name];

        $modules = $controller->getModuleList();
        foreach ($modules as $moduleName) {
          $module = $this->app
            ->requestModule($moduleName);
          if ($module) {
            $controller->addModule($module);
          }
          else {
            Logger::error(
              tr('Module "%1" not found in controller %2', $moduleName, $name));
          }
        }
        $helpers = $controller->getHelperList();
        foreach ($helpers as $helperName) {
          $helper = $this->m
            ->Helpers
            ->getHelper($helperName);
          if ($helper != null) {
            $controller->addHelper($helper);
          }
          else {
            Logger::error(
              tr('Helper "%1" not found in controller %2', $helperName, $name));
          }
        }
      }
      return $this->controllerObjects[$name];
    }
    return null;
  }

  public function addController(Controller $controller) {
    $name = str_replace('Controller', '', get_class($controller));
    $this->controllerObjects[$name] = $controller;
  }

  public function getController($name) {
    if (isset($this->controllerObjects[$name])) {
      return $this->controllerObjects[$name];
    }
    return $this->getInstance($name);
  }

  public function __get($name) {
    return $this->getController($name);
  }
}
