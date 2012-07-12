<?php
// Module
// Name           : Routes
// Version        : 0.3.0
// Description    : The PeanutCMS routing system
// Author         : PeanutCMS
// Dependencies   : errors http templates configuration

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
  
  private $renders = FALSE;

  private $selectedController;

  private $selectedControllerPriority;

  private $selectedControllerParameters = array();

  private $parameters;

  /* Events */
  private $events;
  public function onRendering($h) { $this->events->attach($h); }
  public function onRendered($h) { $this->events->attach($h); }

  protected function init() {
    $this->events = new Events($this);

    $controller = new ApplicationController($this->m->Templates, $this);
    $controller->setRoute('notFound', 1);
    
    $this->addPath('home', 'index', array($this, 'insertParamters'), array());

    $this->Core->onRender(array($this, 'callController'));
  }

  protected function controllerName($controller, $withSuffix = FALSE) {
    if (is_object($controller)) {
      $controller = get_class($controller);
    }
    $substr = substr($controller, -10);
    if ($withSuffix AND $substr != 'Controller') {
      return $controller . 'Controller';
    }
    else if (!$withSuffix AND $substr == 'Controller'){
      return substr($controller, 0, -10);
    }
    else {
      return $controller;
    }
  }
  
  public function getRequest() {
    return $this->request;
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
    $controller = $this->controllerName($controller, TRUE);
    if (isset($this->paths[$controller][$action])) {
      $function = $this->paths[$controller][$action]['function'];
      $additional = $this->paths[$controller][$action]['additional'];
      return call_user_func($function, $parameters, $additional);
    }
    return NULL;
  }

  public function isCurrent($route = NULL) {
    if (!isset($route)) {
      $path = explode('/', $this->m->Configuration->get('http.index.path'));
      return $path == $this->request->path;
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      return $this->isCurrent($route->getRoute());
    }
    else if (is_array($route)) {
      $default = array(
        'path' => NULL,
        'query' => NULL,
        'fragment' => NULL,
        'controller' => $this->selectedController[0],
        'action' => $this->selectedController[1],
        'parameters' => $this->selectedControllerParameters
      );
      if (isset($route['controller'])) {
        $default['action'] = 'index';
        $default['parameters'] = array();
      }
      $route = array_merge($default, $route);
      if (isset($route['path'])) {
        return $this->request->path == $route['path'];
      }
      return $this->controllerName($this->selectedController[0]) == $route['controller']
        AND ($route['action'] == '*' OR $this->selectedController[1] == $route['action'])
        AND ($route['parameters'] == '*' OR $this->selectedControllerParameters == $route['parameters']);
    }
    else {
      return FALSE;
    }
  }

  public function getLink($route = NULL) {
    if (!isset($route)) {
      return $this->m->Http->getLink(array());
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      return $this->getLink($route->getRoute());
    }
    else if (is_array($route)) {
      $default = array(
        'path' => NULL,
        'query' => NULL,
        'fragment' => NULL,
        'controller' => $this->controllerName($this->selectedController[0]),
        'action' => $this->selectedController[1],
        'parameters' => $this->selectedControllerParameters
      );
      if (isset($route['controller']) AND $route['controller'] != $default['controller']) {
        $default['action'] = 'index';
        $default['parameters'] = array();
      }
      $route = array_merge($default, $route);
      if (isset($route['path'])) {
        return $this->m->Http->getLink($route['path'], $route['query'], $route['fragment']);
      }
      if (!isset($route['query'])
          AND $route['controller'] == $default['controller']
          AND $route['action'] == $default['action']
          AND $route['parameters'] == $default['parameters']) {
        $route['query'] = $this->request->query;
      }
      return $this->m->Http->getLink(
        $this->getPath($route['controller'], $route['action'], $route['parameters']),
        $route['query'], $route['fragment']
      );
    }
    else {
      return $route;
    }
  }

  public function redirect($route = NULL) {
    $this->m->Http->redirect(303, $this->getLink($route));
  }

  public function moved($route = NULL) {
    $this->m->Http->redirect(301, $this->getLink($route));
  }

  public function refresh($query = NULL, $fragment = NULL) {
    $this->m->Http->refreshPath($query, $fragment);
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
      'additional' => $additional
    );
  }

  public function setRoute($controller, $priority = 7, $parameters = array()) {
    if ($this->rendered) {
      return FALSE;
    }
    if ($priority > $this->selectedControllerPriority) {
      $this->selectedController = $controller;
      $this->selectedControllerPriority = $priority;
      $this->selectedControllerParameters = $parameters;
    }
  }

  private function mapRoute() {
    if ($this->rendered) {
      return FALSE;
    }
    $routes = $this->routes;
    $path = $this->getRequest()->path;
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

  public function callController($sender, $eventArgs) {
    $this->events->trigger('onRendering');

    $this->mapRoute();
    
    if (!is_null($this->selectedController) AND is_callable($this->selectedController)) {
      $this->rendered = TRUE;
      call_user_func_array($this->selectedController, $this->selectedControllerParameters);
    }
    else {
      /** @todo Don't leave this in .... Don't wait until now to check if controller is callable */
      $this->m->Errors->fatal('Invalid controller',
        tr('%1::%2 is not valid', get_class($this->selectedController[0]), $this->selectedController[1])
      );
    }

    $this->events->trigger('onRendered');
  }

  public function reroute($controller, $action, $parameters = array()) {
    $currentPath = $this->getRequest()->path;
    $actionPath = $this->getPath($controller, $action, $parameters);
    if ($currentPath != $actionPath AND is_array($actionPath)) {
      $this->m->Http->redirectPath($actionPath, $this->getRequest()->query);
    }
  }
}
