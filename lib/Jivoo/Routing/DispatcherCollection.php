<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

class DispatcherCollection {
  private $dispatchers = array();
  
  public function add(IDispatcher $dispatcher) {
    $prefixes = $dispatcher->getPrefixes();
    foreach ($prefixes as $prefix) {
      $this->dispatchers[$prefix] = $dispatcher;
    }
  }
  
  public function validate(&$route) {
    foreach ($this->dispatchers as $dispatcher) {
      if ($dispatcher->validate($route)) {
        break;
      }
    }
  }
  
  public function toRoute($routeString) {
    if (preg_match('/^([^:]+):/', $routeString, $matches) === 1) {
      $prefix = $matches[1];
      if (isset($this->dispatchers[$prefix])) {
        return $this->dispatchers[$prefix]->toRoute($routeString);
      }
    }
    throw new InvalidRouteException(tr('Unknown route prefix.'));
  }
  
}