<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

use Jivoo\Routing\IDispatcher;
use Jivoo\Routing\Routing;
use Jivoo\Routing\InvalidResponseException;

/**
 * Action based routing.
 */
class ActionDispatcher implements IDispatcher {
  /**
   * @var Routing Routing module.
   */
  private $routing;
  
  /**
   * @var Controllers Controllers module;
   */
  private $controllers;
  
  /**
   * Construct url dispatcher.
   * @param Routing $routing Routing module.
   * @param Controllers $controllers Controllers module.
   */
  public function __construct(Routing $routing, Controllers $controllers) {
    $this->routing = $routing;
    $this->controllers = $controllers;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('action');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['controller']) or isset($route['action']);
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    $split = explode('::', substr($routeString, 7));
    $route = array(
      'controller' => $split[0],
      'action' => 'index',
      'parmaters' => array()
    );
    if (isset($split[1]))
      $route['action'] = $split[1];
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return $route['controller'] . '::' . $route['action'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPath($route, $path = null) {
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($route) {
    $controller = $this->controllers->getController($route['controller']);
    if (!isset($controller))
      throw new InvalidRouteException(tr('Invalid controller: %1', $controllerName));
    if (!is_callable(array($controller, $route['action']))) {
      throw new InvalidRouteException(tr(
        'Invalid action: %1',
        $route['controller'] . '::' . $route['action']
      ));
    }
    $controller->before();
    $response = call_user_func_array(array($controller, $route['action']), $route['parameters']);
    if (is_string($response))
      $response = new TextResponse(Http::OK, 'text', $response);
    if (!($response instanceof Response)) {
      throw new InvalidResponseException(tr(
        'An invalid response was returned from action: %1',
        $route['controller'] . '::' . $route['action']
      ));
    }
    $controller->after($response);
    return $response;
  }
}