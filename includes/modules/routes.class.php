<?php
/**
 * Handling routes
 *
 * @pacakge PeanutCMS
 */

/**
 * Routes class
 */
class Routes {

  private $routes;
  
  private $selectedController;
  
  private $selectedControllerPriority;

  public function __construct() {
    global $PEANUT;
    $this->routes = array();
    
    $this->selectedController = NULL;
    $this->selectedControllerPriority = 0;
    
  }

  public function addRoute($path, $controller, $priority = 5) {
    $this->routes[] = array(
      'path' => $path,
      'controller' => $controller,
      'priority' => 5
    );
  }

  public function setRoute($controller, $priority = 7) {
    if ($priority > $this->selectedControllerPriority) {
      $this->selectedController = $controller;
      $this->selectedControllerPriority = $priority;
    }
  }
  
  private function mapRoute() {
    global $PEANUT;
    $routes = $this->routes;
    foreach ($routes as $j => $route) {
      if (count($route['path']) != count($PEANUT['http']->path)) {
        unset($routes[$j]);
      }
    }
    print_r($routes);
    foreach ($PEANUT['http']->path as $i => $fragment) {
      foreach ($routes as $j => $route) {
        if ($route['path'][$i] != $fragment AND $route['path'][$i] != '*') {
          unset($routes[$j]);
        }
      }
    }
    uasort($routes, 'prioritySorter');
    reset($routes);
    $route = current($routes);
    if ($route['priority'] > $this->selectedControllerPriority) {
      $this->selectedController = $route['controller'];
      $this->selectedControllerPriority = $route['priority'];
    }
  }
  
  public function callController() {
    global $PEANUT;
    $this->mapRoute();
    if (!is_null($this->selectedController))
      call_user_func($this->selectedController, $PEANUT['http']->params, 'html');
  }
}