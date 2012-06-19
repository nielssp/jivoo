<?php
// Module
// Name           : Routes
// Version        : 0.3.0
// Description    : The PeanutCMS routing system
// Author         : PeanutCMS
// Dependencies   : errors http templates

/**
 * Handling routes
 *
 * @pacakge PeanutCMS
 */

/**
 * Routes class
 */
class Routes extends ModuleBase {

  private $routes = array();

  private $paths = array();

  private $selectedController;

  private $selectedControllerPriority;

  private $selectedControllerParameters = array();

  private $parameters;

  protected function init() {
    $controller = new ApplicationController($this->m->Templates, $this);
    $controller->setRoute('notFound', 1);
    
    $this->addPath('home', 'index', array($this, 'insertParamters'), array());

    Hooks::attach('render', array($this, 'callController'));
  }

  public function insertParameters($parameters, $additional) {
    $path = $additional[0];
    $result = array();
    foreach ($path as $fragment) {
      if ($fragment == '*') {
        $fragment = array_shift($parameters);
      }
      if ($fragment == '**') {
        while (current($parameters) !== FALSE) {
          $result[] = array_shift($parameters);
        }
        break;
      }
      $result[] = $fragment;
    }
    return $result;
  }

  public function getPath($controller = NULL, $action = 'index', $parameters = array()) {
    if (!isset($controller)) {
      return NULL;
    }
    $controller = className($controller) . 'Controller';
    if (isset($this->paths[$controller][$action])) {
      $function = $this->paths[$controller][$action]['function'];
      $additional = $this->paths[$controller][$action]['parameters'];
      return call_user_func($function, $parameters, $additional);
    }
    return NULL;
  }

  public function getLink($controller = NULL, $action = 'index', $parameters = array()) {
    return $this->m->Http->getLink($this->getPath($controller, $action, $parameters));
  }

  public function redirect($controller = NULL, $action = 'index', $paramters = array(), $query = NULL, $hashtag = NULL) {
    $this->m->Http->redirectPath($this->getPath($controller, $action, $parameters), $query, FALSE, $hashtag);
  }

  public function moved($controller = NULL, $action = 'index', $paramters = array(), $query = NULL, $hashtag = NULL) {
    $this->m->Http->redirectPath($this->getPath($controller, $action, $parameters), $query, TRUE, $hashtag);
  }

  public function refresh($query = NULL, $hashtag = NULL) {
    $this->m->Http->refreshPath($query, $hashtag);
  }

  public function addRoute($path, $controller, $priority = 5) {
    if (!is_array($path)) {
      $path = explode('/', $path);
    }
    $this->routes[] = array(
      'path' => $path,
      'controller' => $controller,
      'priority' => 5,
      'parameters' => array()
    );
    $this->addPath(get_class($controller[0]), $controller[1], array($this, 'insertParameters'), array($path));
  }

  public function addPath($controller, $action, $pathFunction, $additional = array()) {
    if (substr($controller, -10) != 'Controller') {
      $controller .= 'Controller';
    }
    if (!isset($this->paths[$controller])) {
      $this->paths[$controller] = array();
    }
    $this->paths[$controller][$action] = array(
      'function' => $pathFunction,
      'parameters' => $additional
    );
  }

  public function setRoute($controller, $priority = 7, $parameters = array()) {
    if ($priority > $this->selectedControllerPriority) {
      $this->selectedController = $controller;
      $this->selectedControllerPriority = $priority;
      $this->selectedControllerParameters = $parameters;
    }
  }

  private function mapRoute() {
    $routes = $this->routes;
    $path = $this->m->Http->getPath();
    foreach ($routes as $j => $route) {
      if (count($route['path']) != count($path)) {
        if ($route['path'][count($route['path']) - 1] != '**') {
          unset($routes[$j]);
        }
      }
    }
    foreach ($routes as $i => $route) {
      foreach ($route['path'] as $j => $fragment) {
        if ($fragment == '**') {
          $routes[$i]['parameters'] = array_merge($routes[$i]['parameters'], array_slice($path, $j));
          break;
        }
        else if (!isset($path[$j])
                 OR ($fragment  != $path[$j]
                 AND $fragment != '*')) {
          unset($routes[$i]);
          break;
        }
        if ($fragment == '*') {
          $routes[$i]['parameters'][] = $path[$j];
        }
      }
    }
    uasort($routes, 'prioritySorter');
    reset($routes);
    $route = current($routes);
    if ($route['priority'] > $this->selectedControllerPriority) {
      $this->selectedController = $route['controller'];
      $this->selectedControllerPriority = $route['priority'];
      $this->selectedControllerParameters = $route['parameters'];
    }
  }

  public function callController() {
    $this->mapRoute();
    if (!is_null($this->selectedController) AND is_callable($this->selectedController)) {
      call_user_func_array($this->selectedController, $this->selectedControllerParameters);
    }
    else {
      /** @todo Don't leave this in .... Don't wait until now to check if controller is callable */
      $this->m->Errors->fatal('Controller unavailable',
        tr('Hm...: %1', var_dump($this->selectedController)));
    }
  }
}
