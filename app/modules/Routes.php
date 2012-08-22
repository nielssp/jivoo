<?php
// Module
// Name           : Routes
// Version        : 0.3.0
// Description    : The PeanutCMS routing system
// Author         : PeanutCMS
// Dependencies   : Errors Http Templates Editors Configuration

/**
 * Handling routes
 *
 * @pacakge PeanutCMS
 */

/**
 * Routes class
 */
class Routes extends ModuleBase {
  
  private $controllers = array();
  private $helpers = array();

  private $routes = array();

  private $paths = array();
  
  private $rendered = false;

  private $selectedRoute = NULl;

  private $parameters;

  /* Events */
  private $events;
  public function onRendering($h) { $this->events->attach($h); }
  public function onRendered($h) { $this->events->attach($h); }

  protected function init() {
    $this->events = new Events($this);

    $controller = new ApplicationController($this, $this->m->Configuration);
    $controller->setRoute('notFound', 1);
    
    $this->addPath('home', 'index', array($this, 'insertParamters'), array());

    $this->Core->onRender(array($this, 'callController'));
    $this->Core->onModuleLoaded(array($this, 'addAuthModule'));
  }
  
  public function addAuthModule($sender, ModuleLoadedEventArgs $args) {
    if ($args->module != 'Authentication') {
      return;
    }
    foreach ($this->controllers as $controller) {
      $controller->addModule($args->object);
    }
    foreach ($this->helpers as $helper) {
      $helper->addModule($args->object);
    }
  }
  
  public function addController(ApplicationController $controller) {
    $name = substr(get_class($controller), 0, -10);
    $this->controllers[$name] = $controller;
    $controller->addModule($this);
    $controller->addModule($this->m->Templates);
    $controller->addModule($this->m->Editors);
    $auth = $this->Core->requestModule('Authentication');
    if ($auth) {
      $controller->addModule($auth);
    }
  }
  
  public function addHelper(ApplicationHelper $helper) {
    $name = substr(get_class($helper), 0, -6);
    $this->helpers[$name] = $helper;
    $helper->addModule($this);
    $helper->addModule($this->m->Templates);
    $helper->addModule($this->m->Editors);
    $auth = $this->Core->requestModule('Authentication');
    if ($auth) {
      $helper->addModule($auth);
    }
  }

  protected function controllerName($controller, $withSuffix = false) {
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
        while (current($parameters) !== false) {
          $result[] = array_shift($parameters);
        }
        break;
      }
      $result[] = $fragment;
    }
    return $result;
  }

  public function getPath($controller = null, $action = 'index', $parameters = array()) {
    if (!isset($controller)) {
      return null;
    }
    $controller = $this->controllerName($controller, true);
    if (isset($this->paths[$controller][$action])) {
      $function = $this->paths[$controller][$action]['function'];
      $additional = $this->paths[$controller][$action]['additional'];
      return call_user_func($function, $parameters, $additional);
    }
    return null;
  }

  public function isCurrent($route = null) {
    if (!isset($route)) {
      $path = explode('/', $this->m->Configuration->get('http.index.path'));
      return $path == $this->request->path;
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      return $this->isCurrent($route->getRoute());
    }
    else if (is_array($route)) {
      $default = array(
        'path' => null,
        'query' => null,
        'fragment' => null,
        'controller' => $this->selectedRoute['controller'],
        'action' => $this->selectedRoute['action'],
        'parameters' => $this->selectedRoute['parameters']
      );
      if (isset($route['controller'])) {
        $default['action'] = 'index';
        $default['parameters'] = array();
      }
      $route = array_merge($default, $route);
      if (isset($route['path'])) {
        return $this->request->path == $route['path'];
      }
      return $this->controllerName($this->selectedRoute['controller']) == $route['controller']
        AND ($route['action'] == '*' OR $this->selectedRoute['action'] == $route['action'])
        AND ($route['parameters'] == '*' OR $this->selectedRoute['parameters'] == $route['parameters']);
    }
    else {
      return false;
    }
  }

  public function getLink($route = null) {
    if (!isset($route)) {
      return $this->m->Http->getLink(array());
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      return $this->getLink($route->getRoute());
    }
    else if (is_array($route)) {
      if (isset($route['url'])) {
        return $route['url'];
      }
      $default = array(
        'path' => null,
        'query' => null,
        'fragment' => null,
        'controller' => $this->controllerName($this->selectedRoute['controller']),
        'action' => $this->selectedRoute['action'],
        'parameters' => $this->selectedRoute['parameters']
      );
      if (isset($route['controller']) AND $route['controller'] != $default['controller']) {
        $default['action'] = 'index';
        $default['parameters'] = array();
      }
      $route = array_merge($default, $route);
      if (isset($route['query']) AND isset($route['mergeQuery']) AND $route['mergeQuery'] == true) {
        $route['query'] = array_merge($this->request->query, $route['query']);
      }
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

  public function redirect($route = null) {
    $this->m->Http->redirect(303, $this->getLink($route));
  }

  public function moved($route = null) {
    $this->m->Http->redirect(301, $this->getLink($route));
  }

  public function refresh($query = null, $fragment = null) {
    $this->m->Http->refreshPath($query, $fragment);
  }

  public function addRoute($path, ApplicationController $controller, $action, $priority = 5) {
    if (!is_array($path)) {
      $path = explode('/', $path);
    }
    $this->routes[] = array(
      'path' => $path,
      'controller' => $controller,
      'action' => $action,
      'priority' => 5,
      'parameters' => array()
    );
    $this->addPath(get_class($controller), $action, array($this, 'insertParameters'), array($path));
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

  public function setRoute(ApplicationController $controller, $action, $priority = 7, $parameters = array()) {
    if ($this->rendered) {
      return false;
    }
    if ($priority > $this->selectedRoute['priority']) {
      $this->selectedRoute = array(
        'controller' => $controller,
        'action' => $action,
        'priority' => $priority,
        'parameters' => $parameters
      );
    }
  }

  private function mapRoute() {
    if ($this->rendered) {
      return false;
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
    if (isset($route['controller'])) {
      $this->setRoute($route['controller'], $route['action'], $route['priority'], $route['parameters']);
    }
  }

  public function callController($sender, $eventArgs) {
    $this->events->trigger('onRendering');

    $this->mapRoute();
    
    if (isset($this->selectedRoute)
        AND $this->selectedRoute['controller'] instanceof ApplicationController
        AND is_callable(array($this->selectedRoute['controller'], $this->selectedRoute['action']))) {
      $this->rendered = true;
      call_user_func(
        array($this->selectedRoute['controller'], 'init')
      );
      call_user_func_array(
        array($this->selectedRoute['controller'], $this->selectedRoute['action']),
        $this->selectedRoute['parameters']
      );
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
