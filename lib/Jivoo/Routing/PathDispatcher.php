<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Path-array based routing.
 */
class PathDispatcher implements IDispatcher {
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
    return array('path');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['path']);
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    return array('path' => explode('/', substr($routeString, 5)));
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return 'path:' . implode('/', $route['path']);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPath($path, $route) {
    return $route['path'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getLink($route) {
    return $route['url'];
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($route) {
    return $this->routing->redirectPath($route['path']);
  }
}