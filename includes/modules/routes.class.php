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

  public function __construct() {
    $this->routes = array();
  }

  public function addRoute($path, $controller, $priority = 5) {
    $routes[] = array(
      'path' => $path,
      'controller' => $controller,
      'priority' => 5
    );
  }

  public function setRoute($controller, $priority = 7) {

  }
}