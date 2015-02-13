<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

use Jivoo\Routing\IDispatcher;
use Jivoo\Routing\Routing;
use Jivoo\Routing\InvalidResponseException;
use Jivoo\Routing\Response;
use Jivoo\Routing\InvalidRouteException;

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
    if (isset($route['controller']) or isset($route['action'])) {
      if (!isset($route['parameters']))
        $route['parameters'] = array();
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    $split = explode('::', substr($routeString, 7));
    $route = array(
      'controller' => $split[0],
      'action' => 'index',
      'parameters' => array()
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
  public function isCurrent($route) {
    $selection = $this->routing->route;
    return $selection['controller'] == $route['controller']
      and ($route['action'] == '*'
        or $selection['action'] == $route['action'])
      and ($route['parameters'] == '*'
        or $selection['parameters'] == $route['parameters']);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPath($route, $path = null) {
    if (!isset($path))
      return null;
    return $this->routing->insertParameters($route['parameters'], array($path));
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($route) {
    $controller = $this->controllers->getController($route['controller']);
    if (!isset($controller))
      throw new InvalidRouteException(tr('Invalid controller: %1', $route['controller']));
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
