<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Lib;
use Jivoo\Core\Utilities;

/**
 * Controller module. Will automatically find controllers in the controllers
 * directory (and subdirectories). 
 * @package Jivoo\Controllers
 */
class Controllers extends LoadableModule {
  
  /**
   * @var array An associative array of controller names and associated class
   * names.
   */
  private $controllers = array();
  
  /**
   * @var array An associative array of controller names and actions.
   */
  private $actions = array();
  
  /**
   * @var array Associative array of controller names and paths.
   */
  private $paths = array();

  /**
   * @var array An associative array of controller names and associated objects.
   */
  private $controllerObjects = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    if (is_dir($this->p('app', 'controllers'))) {
      $this->findControllers();
    }
  }
  
  /**
   * Find controllers in a directory.
   * @param string $dir Directory.
   */
  private function findControllers($dir = '') {
    $files = scandir($this->p('app', 'controllers/' . $dir));
    if ($files !== false) {
      foreach ($files as $file) {
        if ($file[0] == '.') {
          continue;
        }
        if (is_dir($this->p('app', 'controllers/' . $dir . '/' . $file))) {
          $this->findControllers($dir . '/' . $file);
        }
        else {
          $split = explode('.', $file);
          if (isset($split[1]) and $split[1] == 'php') {
            if ($dir === '')
              $class = $this->app->namespace . '\Controllers\\' . $split[0];
            else
              $class = $this->app->namespace . '\Controllers\\' . str_replace('/', '\\', $dir) . '\\' . $split[0];
            require $this->p('app', 'controllers/' . $file);
            if (Lib::classExists($class) and
                 is_subclass_of($class, 'Jivoo\Controllers\Controller')) {
              $name = str_replace('Controller', '', $split[0]);
              $this->controllers[$name] = $class;
            }
          }
        }
      }
    }
  }
  
  /**
   * Get class name of controller.
   * @param string $controller Controller name.
   * @return string|false Class name or false if not found.
   */
  public function getClass($controller) {
    if (isset($this->controllers[$controller])) {
      return $this->controllers[$controller];
    }
    return false;
  }
  
  /**
   * Get path for controller.
   * @param string $controller Controller name.
   * @return string|false Path or false if not found.
   */
  public function getControllerPath($controller) {
    if (!isset($this->paths[$controller])) {
      $this->paths[$controller] = Utilities::camelCaseToDashes($controller);
    }
    return $this->paths[$controller];
  }
  
  /**
   * Set path of a controller.
   * @param string $controller Controller name..
   * @param string $path Path.
   */
  public function setControllerPath($controller, $path) {
    $this->paths[$controller] = $path;
  }
  
  /**
   * Get list of actions.
   * @param string $controller Controller name.
   * @return string[]|boolean List of actions or false if controller not found. 
   */
  public function getActions($controller) {
    if (isset($this->controllers[$controller])) {
      if (!isset($this->actions[$controller])) {
        $class = $this->controllers[$controller];
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $this->actions[$controller] = array();
        foreach ($methods as $method) {
          if ($method->class == $class) {
            $this->actions[$controller][] = $method->name;
          }
        }
      }
      return $this->actions[$controller];
    }
    return false;
  }
  

  /**
   * Get instance of controller.
   * @param string $name Controller name.
   * @return Controller|null Controller object or null if not found.
   */
  private function getInstance($name) {
    if (!isset($this->controllers[$name])) {
      if (Lib::classExists($name . 'Controller')) {
        $this->controllers[$name] = $name . 'Controller';
      }
      else {
        return null;
      }
    }
    $class = $this->controllers[$name];
    $this->controllerObjects[$name] = new $class($this->app);
    return $this->controllerObjects[$name];
  }

  /**
   * Add a controller object.
   * @param Controller $controller Controller object.
   */
  public function addController(Controller $controller) {
    $name = str_replace('Controller', '', get_class($controller));
    $this->controllers[$name] = get_class($controller);
    $this->controllerObjects[$name] = $controller;
  }

  /**
   * Get a controller object.
   * @param string $name Controller name.
   * @return Controller|null Controller object or null if not found.
   */
  public function getController($name) {
    if (isset($this->controllerObjects[$name])) {
      return $this->controllerObjects[$name];
    }
    return $this->getInstance($name);
  }

  /**
   * Get a controller object.
   * @param string $name Controller name.
   * @return Controller|null Controller object or null if not found.
   */
  public function __get($name) {
    return $this->getController($name);
  }
}
