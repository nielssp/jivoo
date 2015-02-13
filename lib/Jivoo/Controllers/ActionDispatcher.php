<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Controllers;

/**
 * Action based routing.
 */
class ActionDispatcher implements IDispatcher {
  /**
   * @var Routing Routing module.
   */
  private $routing;
  
  /**
   * Construct url dispatcher.
   * @param Routing $routing Routing module.
   */
  public function __construct(Routing $routing) {
    $this->routing = $routing;
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
  public function dispatch($route) {
  }
}