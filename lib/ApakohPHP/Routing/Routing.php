<?php
// Module
// Name           : Routing
// Version        : 0.3.0
// Description    : The ApakohPHP routing system
// Author         : apakoh.dk

/**
 * Handling routes
 *
 * @pacakge ApakohPHP
 * @subpackage Routing
 */
class Routing extends ModuleBase {

  private $controllers = array();
  private $helpers = array();

  private $routes = array();

  private $paths = array();

  private $rendered = false;

  private $selectedRoute = null;

  private $parameters;

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
      if (isset($this->request->path[0]) AND $this->request->path[0] == 'index.php') {
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

    $controller = new ApplicationController($this, $this->config);
    $controller->setRoute('notFound', 1);

    $this->addPath('home', 'index', array($this, 'insertParamters'), array());

    $this->app->onRender(array($this, 'callController'));
  }


  public function addController(ApplicationController $controller) {
    $name = substr(get_class($controller), 0, -10);
    $this->controllers[$name] = $controller;
  }

  public function addHelper(ApplicationHelper $helper) {
    $name = substr(get_class($helper), 0, -6);
    $this->helpers[$name] = $helper;
    $helper->addModule($this);
    $helper->addModule($this->app->Templates);
    $helper->addModule($this->app->Editors);
    $auth = $this->app->requestModule('Authentication');
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

  public function getPath($controller = null, $action = 'index',
                          $parameters = array()) {
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

  public function getLink($route = null) {
    if (!isset($route)) {
      return $this->getLinkFromPath(array());
    }
    else if (is_object($route) AND is_a($route, 'ILinkable')) {
      return $this->getLink($route->getRoute());
    }
    else if (is_array($route)) {
      if (isset($route['url'])) {
        return $route['url'];
      }
      $default = array('path' => null, 'query' => null, 'fragment' => null,
        'controller' => $this->controllerName(
            $this->selectedRoute['controller']),
        'action' => $this->selectedRoute['action'],
        'parameters' => $this->selectedRoute['parameters']
      );
      if (isset($route['controller'])
          AND $route['controller'] != $default['controller']) {
        $default['action'] = 'index';
        $default['parameters'] = array();
      }
      $route = array_merge($default, $route);
      if (isset($route['query']) AND isset($route['mergeQuery'])
          AND $route['mergeQuery'] == true) {
        $route['query'] = array_merge($this->request
          ->query, $route['query']);
      }
      if (isset($route['path'])) {
        return $this->getLinkFromPath($route['path'], $route['query'], $route['fragment']);
      }
      if (!isset($route['query'])
          AND $route['controller'] == $default['controller']
          AND $route['action'] == $default['action']
          AND $route['parameters'] == $default['parameters']) {
        $route['query'] = $this->request
          ->query;
      }
      return $this->getLinkFromPath(
        $this->getPath($route['controller'], $route['action'], $route['parameters']),
        $route['query'],
        $route['fragment']
      );
    }
    else {
      return $route;
    }
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
    $this->redirect($status, $this->getLink($path, $query, $fragment));
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

  public function addRoute($path, ApplicationController $controller, $action,
                           $priority = 5) {
    if (!is_array($path)) {
      $path = explode('/', $path);
    }
    $this->routes[] = array('path' => $path, 'controller' => $controller,
      'action' => $action, 'priority' => 5, 'parameters' => array()
    );
    $this->addPath(get_class($controller), $action,
        array($this, 'insertParameters'), array($path));
  }

  public function addPath($controller, $action, $pathFunction,
                          $additional = array()) {
    if (substr($controller, -10) != 'Controller') {
      $controller .= 'Controller';
    }
    if (!isset($this->paths[$controller])) {
      $this->paths[$controller] = array();
    }
    $this->paths[$controller][$action] = array('function' => $pathFunction,
      'additional' => $additional
    );
  }

  public function setRoute(ApplicationController $controller, $action,
                           $priority = 7, $parameters = array()) {
    if ($this->rendered) {
      return false;
    }
    if ($priority > $this->selectedRoute['priority']) {
      $this->selectedRoute = array('controller' => $controller,
        'action' => $action, 'priority' => $priority,
        'parameters' => $parameters
      );
    }
  }

  private function mapRoute() {
    if ($this->rendered) {
      return false;
    }
    $routes = $this->routes;
    $path = $this->getRequest()
      ->path;
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
          $routes[$i]['parameters'] = array_merge($routes[$i]['parameters'],
            array_slice($path, $j));
          break;
        }
        else if (!isset($path[$j])
            OR ($fragment != $path[$j] AND $fragment != '*')) {
          unset($routes[$i]);
          break;
        }
        if ($fragment == '*') {
          $routes[$i]['parameters'][] = $path[$j];
        }
      }
    }
    uasort($routes, array('Utilities', 'prioritySorter'));
    reset($routes);
    $route = current($routes);
    if (isset($route['controller'])) {
      $this->setRoute($route['controller'], $route['action'],
          $route['priority'], $route['parameters']);
    }
  }

  public function callController($sender, $eventArgs) {
    $this->events
      ->trigger('onRendering');

    $this->mapRoute();

    if (isset($this->selectedRoute)
        AND $this->selectedRoute['controller'] instanceof ApplicationController
        AND is_callable(
          array($this->selectedRoute['controller'],
            $this->selectedRoute['action']
          ))) {
      $controller = $this->selectedRoute['controller'];
      $controller->addModule($this);
      $controller->addModule($this->app->requestModule('Templates'));
      $controller->addModule($this->app->requestModule('Editors'));
      $controller->addModule($this->app->requestModule('Authentication'));
      $this->rendered = true;
      call_user_func(array($controller, 'init'));
      call_user_func_array(
        array($controller, $this->selectedRoute['action']),
        $this->selectedRoute['parameters']
      );
    }
    else {
      /** @todo Don't leave this in .... Don't wait until now to check if controller is callable */
      throw new Exception(tr(
        '%1#%2 is not a valid action',
        get_class($this->selectedController[0]),
        $this->selectedController[1]
      ));
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
