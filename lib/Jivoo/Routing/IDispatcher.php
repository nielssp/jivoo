<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * Implements a type of route.
 */
interface IDispatcher {
  /**
   * Get route string prefixes understood by this dispatcher.
   * @return string[] List of prefixes.
   */
  public function getPrefixes();
  
  /**
   * Validate a route.
   * @param array $route Route array.
   * @return bool True if route understood by dispatcher. 
   */
  public function validate(&$route);
  
  /**
   * Convert from a route string.
   * @param string $routeString Route string, e.g. 'prefix:ClassName'.
   * @return array Route array.
   */
  public function toRoute($routeString);
  
  /**
   * Convert to a route string.
   * @param array $route Route array.
   * @return string Route string.
   */
  public function fromRoute($route);
  
  /**
   * Get path for route.
   * @param string[] $path Path pattern array.
   * @param array $route Route array.
   * @return string[] Path.
   */
  public function getPath($path, $route);
  
  /**
   * Get link from route
   * @param array $route Route array.
   * @return string|null Relative or absolute link or null.
   */
  public function getLink($route);
  
  /**
   * Respond to a route array.
   * @param array $route Route array.
   * @return Response|string Response object or content.
   */
  public function dispatch($route);
}