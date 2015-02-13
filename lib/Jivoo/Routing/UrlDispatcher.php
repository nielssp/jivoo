<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Implements a type of route.
 */
class UrlDispatcher implements IDispatcher {
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
    return array('http', 'https', 'url');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    return isset($route['url']);
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    if (strncmp($routeString, 'url:', 4))
      return array('url' => substr($routeString, 4));
    return array('url' => $routeString);
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    $url = $route['url'];
    if (preg_match('/^https?:/', $url) === 1)
      return $url;
    return 'url:' . $url;
  }

  /**
   * {@inheritdoc}
   */
  public function dispatch($route) {
    return $this->routing->redirect($route);
  }
}