<?php
/**
 * Handling routes
 *
 * @pacakge PeanutCMS
 */

/**
 * Routes class
 */
class Routes implements IModule {

  private $errors;

  private $http;

  private $routes;

  private $selectedController;

  private $selectedControllerPriority;

  public function __construct(Errors $errors, Http $http) {
    $this->errors = $errors;
    $this->http = $http;

    $this->routes = array();

    $this->setRoute(array($this, 'notFoundController'), 1);
  }

  public static function getDependencies() {
    return array('errors', 'http');
  }


  public function addRoute($path, $controller, $priority = 5) {
    if (!is_array($path)) {
      $path = explode('/', $path);
    }
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
    $routes = $this->routes;
    foreach ($routes as $j => $route) {
      if (count($route['path']) != count($PEANUT['http']->path)) {
        if ($route['path'][count($route['path']) - 1] != '**') {
          unset($routes[$j]);
        }
      }
    }
    foreach ($routes as $i => $route) {
      foreach ($route['path'] as $j => $fragment) {
        if ($fragment == '**') {
          break;
        }
        else if (!isset($PEANUT['http']->path[$j])
                 OR ($fragment  != $PEANUT['http']->path[$j]
                 AND $fragment != '*')) {
          unset($routes[$i]);
          break;
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
    $this->mapRoute();
    if (!is_null($this->selectedController) AND is_callable($this->selectedController)) {
      call_user_func($this->selectedController, $this->http->params, 'html');
    }
    else {
      /** @todo Don't leave this in .... Don't wait until now to check if controller is callable */
      $this->errors->fatal('You are not supposed  to see this',
      						   'Please contact the developer and tell him, that he is and idiot...');
    }
  }

  public function notFoundController($parameters = array(), $contentType = 'html') {
    $templateData = array();

    //$PEANUT['templates']->renderTemplate('404.html', $templateData);
  }
}