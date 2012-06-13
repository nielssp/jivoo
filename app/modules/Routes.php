<?php
// Module
// Name           : Routes
// Version        : 0.2.0
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

  private $routes;

  private $selectedController;

  private $selectedControllerPriority;

  protected function init() {
    $this->routes = array();

    $this->setRoute(array($this, 'notFoundController'), 1);

    Hooks::attach('render', array($this, 'callController'));
  }

  public function getPath() {
    return $this->m->Http->getPath();
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
          break;
        }
        else if (!isset($path[$j])
                 OR ($fragment  != $path[$j]
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
      call_user_func($this->selectedController, $this->m->Http->getPath(), $this->m->Http->getParams(), 'html');
    }
    else {
      /** @todo Don't leave this in .... Don't wait until now to check if controller is callable */
      $this->errors->fatal('You are not supposed  to see this',
      						   'Please contact the developer and tell him, that he is and idiot...');
    }
  }

  public function notFoundController($parameters = array(), $contentType = 'html') {
    $templateData = array();

    $this->m->Templates->renderTemplate('404.html', $templateData);
  }
}
