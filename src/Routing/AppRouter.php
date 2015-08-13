<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

use Jivoo\Core\AppListener;

/**
 * An application listener that can be used to implement custom routing.
 */
abstract class AppRouter extends AppListener {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing');

  /**
   * {@inheritdoc}
   */
  protected $handlers = array(
    'Jivoo\Routing\Routing.beforeLoadRoutes',
    'Jivoo\Routing\Routing.afterLoadRoutes'
  );

  /**
   * @var bool Whether or not to load routes before the application routes.
   */
  protected $beforeConfig = false;
  
  /**
   * @var int Priority of routes created by class.
   */
  protected $priority = 7;
  
  /**
   * Is called before the applications routes are loaded.
   */
  public function beforeLoadRoutes() {
    if ($this->beforeConfig) {
      $this->createRoutes($this->m->Routing->routes);
      $current = $this->m->Routing->route;
      if (is_array($current) and isset($current['priority'])) {
        if ($current['priority'] > $this->priority)
          return;
      }
      if ($this->checkPath($this->request->path)) {
        
      }
    }
  }

  /**
   * Is called after the application routes are loaded.
   */
  public function afterLoadRoutes() {
    if (!$this->beforeConfig) {
      $this->createRoutes($this->m->Routing->routes);
      $current = $this->m->Routing->route;
      if (is_array($current) and isset($current['priority'])) {
        if ($current['priority'] > $this->priority)
          return;
      }
      if ($this->checkPath($this->request->path)) {
        
      }
    }
  }

  /**
   * Create custom routes on the routing table.
   * @param RoutingTable $routes Routing table.
   */
  protected abstract function createRoutes(RoutingTable $routes);

  /**
   * Use to manually convert a path (the current path) to a route. Use
   * {@see setRoute} to set the current route.
   * @param string[] Path array.
   * @return bool True if path was recognized, false otherwise. 
   */
  public function checkPath($path) {
    return false;
  }

  /**
   * Convert a route to a path
   * @param array $route Route array.
   * @param string[]|null $path Path pattern array or null if no associated path.
   * @return string[]|null Path.
   */
  public function getPath($route, $path = null) {
    return null;
  }

  /**
   * Set up this {@see AppRouter} as the creator of paths for a specific route. 
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param int|string $arity Arity of route, i.e. number of parameters (integer or '*').
   * @param int $priority Priority of route.
   */
  protected function reroute($route, $arity, $priority = null) {
    if (!isset($priority))
      $priority = $this->priority;
    $this->m->Routing->addPathFunction($route, array($this, 'getPath'), $arity, $priority);
  }

  /**
   * Set the current route.
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   * @param int $priority Priority of route.
   */
  protected function setRoute($route, $priority = null) {
    if (!isset($priority))
      $priority = $this->priority;
    $this->m->Routing->setRoute($route, $priority);
  }
}