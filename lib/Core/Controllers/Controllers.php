<?php
// Module
// Name           : Controllers
// Version        : 0.3.14
// Description    : For contollers
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates
//                  Core/Helpers Core/Models

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
    $this->findControllers();
  }
  
  private function findControllers($dir = '') {
    Lib::addIncludePath($this->p('controllers', $dir));
    $handle = opendir($this->p('controllers', $dir));
    while ($file = readdir($handle)) {
      if ($file[0] == '.') {
        continue;
      }
      if (is_dir($this->p('controllers', $file))) {
        if ($dir == '') {
          $this->findControllers($file);
        }
        else {
          $this->findControllers($dir . '/' . $file);
        }
      }
      else {
        $split = explode('.', $file);
        if (isset($split[1]) AND $split[1] == 'php') {
          $class = $split[0];
          if (Lib::classExists($class) AND is_subclass_of($class, 'Controller')) {
            $name = str_replace('Controller', '', $class);
            $this->controllers[$name] = $class;
          }
        }
      }
    }
    closedir($handle);
  }

  private function getInstance($name) {
    if (isset($this->controllers[$name])) {
      if (!isset($this->controllerObjects[$name])) {
        $class = $this->controllers[$name];
        $this->controllerObjects[$name] = new $class(
          $this->m->Routing, $this->m->Templates,
          $this->app->config
        );
        $controller = $this->controllerObjects[$name];

        $modules = $controller->getModuleList();
        foreach ($modules as $moduleName) {
          $module = $this->app->requestModule($moduleName);
          if ($module) {
            $controller->addModule($module);
          }
          else {
            Logger::error(
              tr('Module "%1" not found for controller %2', $moduleName, $name)
            );
          }
        }
        $this->m->Helpers->addHelpers($controller);
        $this->m->Models->addModels($controller);
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
