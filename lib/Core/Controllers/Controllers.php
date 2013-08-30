<?php
// Module
// Name           : Controllers
// Description    : For contollers
// Author         : apakoh.dk
// Dependencies   : Core/Routing Core/Templates
//                  Core/Helpers Core/Models

/**
 * Controller module. Will automatically find controllers in the controllers
 * directory (and subdirectories). 
 * @package Core\Controllers
 */
class Controllers extends ModuleBase {

  /**
   * @var array An associative array of controller names and associated class
   * names
   */
  private $controllers = array();
  
  /**
   * @var array An associative array of controller names and actions
   */
  private $actions = array();
  
  /**
   * @var array Associative array of controller names and paths
   */
  private $paths = array();

  /**
   * @var array An associative array of controller names and associated objects
   */
  private $controllerObjects = array();

  protected function init() {
    $this->findControllers();
  }
  
  /**
   * Find controllers 
   * @param string $dir Directory
   */
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
  
  /**
   * Get class name of controller
   * @param string $controller Controller name
   * @return string|false Class name or false if not found
   */
  public function getClass($controller) {
    if (isset($this->controllers[$controller])) {
      return $this->controllers[$controller];
    }
    return false;
  }
  
  /**
   * Get path for controller
   * @param string $controller Controller name
   * @return string|false Path or false if not found
   */
  public function getControllerPath($controller) {
    if (!isset($this->paths[$controller])) {
      $this->paths[$controller] = Utilities::camelCaseToDashes($controller);
    }
    return $this->paths[$controller];
  }
  
  public function setControllerPath($controller, $path) {
    $this->paths[$controller] = $path;
  }
  
  /**
   * Get list of actions
   * @param string $controller Controller name
   * @return string[]|boolean List of actions or false if controller not found 
   */
  public function getActions($controller) {
    if (isset($this->controllers[$controller])) {
      if (!isset($this->actions[$controller])) {
        $class = $this->controllers[$controller];
        $classMethods = get_class_methods($class);
        $parentMethods = get_class_methods('Controller');
        $this->actions[$controller] = array_diff($classMethods, $parentMethods);
      }
      return $this->actions[$controller];
    }
    return false;
  }
  
  /**
   * 
   */

  /**
   * Get instance of controller
   * @param string $name Controller name
   * @return Controller|null Controller object or null if not found
   */
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

  /**
   * Add a controller object
   * @param Controller $controller Controller object
   */
  public function addController(Controller $controller) {
    $name = str_replace('Controller', '', get_class($controller));
    $this->controllers[$name] = get_class($controller);
    $this->controllerObjects[$name] = $controller;
  }

  /**
   * Get a controller object
   * @param string $name Controller name
   * @return Controller|null Controller object or null if not found
   */
  public function getController($name) {
    if (isset($this->controllerObjects[$name])) {
      return $this->controllerObjects[$name];
    }
    return $this->getInstance($name);
  }

  /**
   * Get a controller object
   * @param string $name Controller name
   * @return Controller|null Controller object or null if not found
   */
  public function __get($name) {
    return $this->getController($name);
  }
}
