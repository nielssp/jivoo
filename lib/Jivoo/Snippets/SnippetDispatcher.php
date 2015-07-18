<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Routing\IDispatcher;
use Jivoo\Routing\Routing;
use Jivoo\Routing\InvalidResponseException;
use Jivoo\Routing\Response;
use Jivoo\Routing\InvalidRouteException;
use Jivoo\Routing\RoutingTable;
use Jivoo\Routing\TextResponse;
use Jivoo\Routing\Http;
use Jivoo\Core\Json;
use Jivoo\Core\ClassNotFoundException;

/**
 * Snippet based routing.
 */
class SnippetDispatcher implements IDispatcher {
  /**
   * @var Routing Routing module.
   */
  private $routing;
  
  /**
   * @var Snippets Snippets module;
   */
  private $snippets;
  
  /**
   * Construct url dispatcher.
   * @param Routing $routing Routing module.
   * @param Snippets $snippets Snippets module.
   */
  public function __construct(Routing $routing, Snippets $snippets) {
    $this->routing = $routing;
    $this->snippets = $snippets;;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPrefixes() {
    return array('snippet');
  }

  /**
   * {@inheritdoc}
   */
  public function validate(&$route) {
    if (isset($route['snippet'])) {
      if (!isset($route['parameters']))
        $route['parameters'] = array();
      return true;
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function autoRoute(RoutingTable $table, $route, $resource = false) {
    $class = $route['snippet'];
    $dirs = explode('\\', $class);
    $dirs = array_map(array('Jivoo\Core\Utilities', 'camelCaseToDashes'), $dirs);
    $pattern = 'ANY ' . str_replace('_', '.', implode('/', $dirs));
    $table->match($pattern, $route);
    return $pattern;
  }

  /**
   * {@inheritdoc}
   */
  public function toRoute($routeString) {
    if (preg_match('/^snippet:([a-z0-9_\\\\]+)(\[.*\])?$/i', $routeString, $matches) !== 1)
      throw new InvalidRouteException(tr('Invalid route string for snippet dispatcher'));
    $route = array(
      'parameters' => array()
    );
    if (isset($matches[2])) {
      $route['parameters'] = Json::decode($matches[2]);
      if (!is_array($route['parameters']))
        throw new InvalidRouteException(tr('Invalid JSON parameters in route string'));
    }
    $route['snippet'] = $matches[1];
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return 'snippet:' . $route['snippet'];
  }

  /**
   * {@inheritdoc}
   */
  public function isCurrent($route) {
    $selection = $this->routing->route;
    return $selection['snippet'] == $route['snippet'] 
      and ($route['parameters'] == '*'
        or $selection['parameters'] == $route['parameters']);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPath($route, $path = null) {
    if (!isset($path))
      return null;
    return Routing::insertParameters($route['parameters'], $path);
  }

  /**
   * {@inheritdoc}
   */
  public function createDispatch($route) {
    $snippet = $this->snippets->getSnippet($route['snippet']);
    if (!isset($snippet))
      throw new InvalidRouteException(tr('Invalid snippet: %1', $route['snippet']));
    return function() use($snippet, $route) {
      $snippet->enableLayout();
      $response = $snippet($route['parameters']);
      if (is_string($response))
        $response = new TextResponse($snippet->getStatus(), 'text/html', $response);
      return $response;
    };
  }
}
