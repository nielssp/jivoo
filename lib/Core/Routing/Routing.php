<?php
// Module
// Name           : Routing
// Version        : 0.3.0
// Description    : The Apakoh Core routing system
// Author         : apakoh.dk

/**
 * Handling routes
 *
 * @pacakge Core
 * @subpackage Routing
 */
class Routing extends ModuleBase {
  
  /** @deprecated */
  private $matchTree = array();

  private $controllers = null;
  
  /** @deprecated */
  private $routes = array();
  
  private $selection = array('route' => null, 'priority' => 0);

  private $paths = array();

  private $rendered = false;

  /** @deprecated */
  private $selectedRoute = null;

  /* Events */
  private $events;
  public function onRendering($h) { $this->events->attach($h); }
  public function onRendered($h) { $this->events->attach($h); }

  protected function init() {
    $this->events = new Events($this);
    
    // Set default settings
    $this->config->defaults = array(
      'rewrite' => false,
      'index' => array(
        'path' => 'posts',
        'query' => array()
      ),
      'sessionPrefix' => $this->app->sessionPrefix,
    );

    $this->request = new Request($this->config['sessionPrefix']);

    // Determine if the current URL is correct
    if ($this->config['rewrite']) {
      if (isset($this->request->path[0]) AND $this->request->path[0] == 'index.php') {;
        if (count($this->request->path) <= 1) {
          $this->redirectPath(array(), $this->request->query);
        }
        else {
          array_shift($this->request->path);
          $this->redirectPath($this->request->path, $this->request->query);
        }
      }
    }
    else {
      if (!isset($this->request->path[0]) OR $this->request->path[0] != 'index.php') {
        $this->redirectPath($this->request->path, $this->request->query);
      }
      $path = $this->request->path;
      array_shift($path);
      $this->request->path = $path;
    }
    
    $path = explode('/', $this->config['index']['path']);
    $query = $this->config['index']->get('query', true);
    if (count($this->request->path) < 1) {
      $this->request->path = $path;
      $this->request->query = array_merge($query, $this->request->query);
    }
    else if ($path == $this->request->path) {
      $this->redirectPath(array(), $this->request->query);
    }

//     $this->addPath('home', 'index', array($this, 'insertParamters'), array());

    $this->app->onRender(array($this, 'findRoute'));
  }
  
  public function setRoot($route) {
    /** @todo unimplemented */
  }
  
  public function setError($route) {
    /** @todo unimplemented */
  }
  

  protected function controllerName($controller, $withSuffix = false) {
    if (is_object($controller)) {
      $controller = get_class($controller);
    }
    $substr = substr($controller, -10);
    if ($withSuffix AND $substr != 'Controller') {
      return $controller . 'Controller';
    }
    else if (!$withSuffix AND $substr == 'Controller') {
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
      if ($fragment == '**' || $fragment == ':*') {
        while (current($parameters) !== false) {
          $result[] = array_shift($parameters);
        }
        break;
      }
      $result[] = $fragment;
    }
    return $result;
  }

  public function getPath($controller = null, $action = 'index',
                          $parameters = array()) {
    if (!isset($controller)) {
      return null;
    }
    $controller = $this->controllerName($controller);
    if (isset($this->paths[$controller][$action])) {
      $function = $this->paths[$controller][$action]['function'];
      $additional = $this->paths[$controller][$action]['additional'];
      return call_user_func($function, $parameters, $additional);
    }
    throw new InvalidRouteException(tr('Could not find path for ' . $controller . '::' . $action));
  }

  public function isCurrent($route = null) {
    if (!isset($route)) {
      $path = explode('/', $this->config['index']['path']);
      return $path == $this->request
            ->path;
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      return $this->isCurrent($route->getRoute());
    }
    else if (is_array($route)) {
      $default = array('path' => null, 'query' => null, 'fragment' => null,
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
        return $this->request
          ->path == $route['path'];
      }
      return $this->controllerName($this->selectedRoute['controller'])
        == $route['controller']
        AND ($route['action'] == '*'
          OR $this->selectedRoute['action'] == $route['action'])
        AND ($route['parameters'] == '*'
          OR $this->selectedRoute['parameters'] == $route['parameters']);
    }
    else {
      return false;
    }
  }
  
  /**
   * Create a link to a page
   *
   * @param array $path Path as an array
   * @return string Link
   */
  public function getLinkFromPath($path = null, $query = null, $fragment = null,
    $rewrite = false) {
    if (!isset($path)) {
      $path = $this->request
      ->path;
    }
    $index = explode('/', $this->config['index']['path']);
    if ($index == $path) {
      $path = array();
    }
    if (isset($fragment)) {
      $fragment = '#' . $fragment;
    }
    else {
      $fragment = '';
    }
    if (is_array($query) AND count($query) > 0) {
      $queryStrings = array();
      foreach ($query as $key => $value) {
        if ($value == '') {
          $queryStrings[] = urlencode($key);
        }
        else {
          $queryStrings[] = urlencode($key) . '=' . urlencode($value);
        }
      }
      $combined = implode('/', $path) . '?' . implode('&', $queryStrings)
      . $fragment;
      if ($this->config['rewrite'] OR $rewrite) {
        return $this->w($combined);
      }
      else {
        return $this->w('index.php/' . $combined);
      }
    }
    else {
      if ($this->config['rewrite'] OR $rewrite) {
        return $this->w(implode('/', $path) . $fragment);
      }
      else {
        return $this->w('index.php/' . implode('/', $path) . $fragment);
      }
    }
  }

  public function validateRoute($route) {
    if (!isset($route)) {
      return array('path' => array(), 'query' => null, 'fragment' => null);
    }
    if (is_string($route)) {
      if (strpos($route, '/') !== false) {
        return array('url' => $route);
      }
      $parts = explode('::', $route);
      $route = array();
      if (isset($parts[0])) {
        $route['controller'] = $parts[0];
        if (isset($parts[1])) {
          $route['action'] = $parts[1];
          $route['parameters'] = array();
          for ($i = 2; $i < count($parts); $i++) {
            $route['parameters'][] = $parts[$i];
          }
        }
      }
    }
    else if (is_object($route) AND $route instanceof ILinkable) {
      return $this->validateRoute($route->getRoute());
    }
    if (!is_array($route)) {
      throw new InvalidRouteException(tr('Not a valid route, must be array or string'));
    }
    if (isset($route['url'])) {
      return $route;
    }
    if (isset($route['query'])){
      if (isset($route['mergeQuery']) AND $route['mergeQuery'] == true) {
        $route['query'] = array_merge($this->request->query, $route['query']);
      }
    }
    else {
      $route['query'] = null;
    }
    if (!isset($route['fragment'])) {
      $route['fragment'] = null;
    }
    if (isset($route['path'])) {
      return $route;
    }
    if (isset($route['controller'])) {
      $route['controller'] = $this->controllerName($route['controller']);
      if (!isset($route['action'])) {
        $route['action'] = 'index';
      }
      if (!isset($route['parameters'])) {
        $route['parameters'] = array();
      }
    }
    else if (isset($this->selection['route']['controller'])) {
      $route['controller'] = $this->selection['route']['controller'];
      if (!isset($route['action'])) {
        $route['action'] = $this->selection['route']['action'];
      }
      if (!isset($route['parameters'])) {
        $route['parameters'] = $this->selection['route']['parameters'];
      }
    }
    return $route;
  }
  
  public function getLink($route = null) {
    $route = $this->validateRoute($route);
    if (isset($route['url'])) {
      return $route['url'];
    }
    if (isset($route['path'])) {
      return $this->getLinkFromPath($route['path'], $route['query'], $route['fragment']);
    }
    if (isset($route['controller']) AND isset($route['action'])) {
      return $this->getLinkFromPath(
        $this->getPath($route['controller'], $route['action'], $route['parameters']),
        $route['query'],
        $route['fragment']
      );
    }
    throw new InvalidRouteException(tr('Incomplete route'));
  }
  
  /**
   * An internal redirect
   *
   * @param array $path A new path
   * @param array $parameters Additional parameters
   * @param bool $moved If true (default) then a 301 status code will be used,
   * if false then a 303 status code will be used
   * @return void
   */
  public function redirectPath($path = null, $query = null, $moved = true,
    $fragment = null, $rewrite = false) {
    $status = $moved ? 301 : 303;
    Http::redirect($status, $this->getLinkFromPath($path, $query, $fragment));
  }

  public function redirect($route = null) {
    Http::redirect(303, $this->getLink($route));
  }

  public function moved($route = null) {
    Http::redirect(301, $this->getLink($route));
  }

  public function refresh($query = null, $fragment = null) {
    if (!isset($query)) {
      $query = $this->request
      ->query;
    }
    $this->redirectPath($this->request->path, $query, false, $fragment);
    $this->refresh($query, $fragment);
  }
  
  public function addRoute($pattern, $route, $priority = 5) {
    $route = $this->validateRoute($route);
    
//     Logger::debug('Add route: ' . $pattern . ' -> ' . $route['controller'] . '::' . $route['action']);
    $pattern = explode('/', $pattern);
    
    $path = $this->request->path;
    $isMatch = true;
    foreach ($pattern as $j => $part) {
      if ($part == '**' || $part == ':*') {
        $route['parameters'] = array_merge(
          $route['parameters'],
          array_slice($path, $j)
        );
        break;
      }
      else if (!isset($path[$j])) {
        $isMatch = false;
        break;
      }
      if ($path[$j] == $part) {
        continue;
      }
      if ($part == '*') {
        $route['parameters'][] = $path[$j];
        continue;
      }
      if ($part[0] == ':') {
        $var = substr($part, 1);
        if (is_numeric($var)) {
          $route['parameters'][(int)$var] = $path[$j];
        }
        else if ($var == ':controller') {
          $route['controller'] = Utilities::dashesToCamelCase($path[$j]);
        }
        else if ($var == ':action') {
          $route['action'] = lcfirst(Utilities::dashesToCamelCase($path[$j]));
        }
        else {
          throw new Exception(tr('Unknown pattern "%1" in route configuration', $part));
        }
        continue;
      }
      $isMatch = false;
      break;
    }
    if ($isMatch) {
      if ($priority > $this->selection['priority']) { // or >= ??
        $this->selection['route'] = $route;
      }
    }
    if (isset($route['controller']) AND isset($route['action'])) {
      $this->addPath(
        $route['controller'], $route['action'],
        array($this, 'insertParameters'), array($pattern)
      );
    }
  }

  public function addPath($controller, $action, $pathFunction,
                          $additional = array()) {
    $controller = $this->controllerName($controller);
    if (!isset($this->paths[$controller])) {
      $this->paths[$controller] = array();
    }
    $this->paths[$controller][$action] = array(
      'function' => $pathFunction,
      'additional' => $additional
    );
  }

  public function setRoute($controller, $action,
                           $priority = 7, $parameters = array()) {
    if ($this->rendered) {
      return false;
    }
    $controller = $this->controllerName($controller);
    if ($priority > $this->selection['priority']) {
      $this->selection['route'] = array(
        'controller' => $controller,
        'action' => $action,
        'parameters' => $parameters
      );
    }
  }


  /** @todo WIP */
  private function drawRoutes() {
    $routeFile = $this->p('config', 'routes.php');
    if (file_exists($routeFile)) {
      $routes = include $routeFile;
      foreach ($routes as $route) {
        $route->draw($this, $this->controllers);
      }
    }
  }
  
  /** @todo WIP */
  public function findRoute() {
    $this->events->trigger('onRendering');
    
    $this->controllers = $this->app->requestModule('Controllers');
    if (!$this->controllers) {
      throw new Exception('Missing controllers module');
    }
    
    $this->drawRoutes();
    
    if ($this->selection['route'] == null) {
      throw new Exception(tr('No route selected'));
    }
    
    $route = $this->selection['route'];
    
    if (isset($route['controller'])) {
      $controller = $this->controllers->getController($route['controller']);
      if (!$controller) {
        throw new Exception(tr('Invalid controller: %1', $route['controller']));
      }
      $action = $route['action'];
      $this->rendered = true;
      $controller->preRender();
      call_user_func_array(array($controller, $action), $route['parameters']);
    }
    
    $this->events->trigger('onRendered');
  }

  public function reroute($controller, $action, $parameters = array()) {
    $currentPath = $this->request->path;
    $actionPath = $this->getPath($controller, $action, $parameters);
    if ($currentPath != $actionPath AND is_array($actionPath)) {
      $this->redirectPath($actionPath, $this->getRequest()->query);
    }
  }
}

class InvalidRouteException extends Exception { }