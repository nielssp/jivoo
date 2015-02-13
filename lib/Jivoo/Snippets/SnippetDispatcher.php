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

/**
 * Snippet based routing.
 */
class SnippetDispatcher implements IDispatcher {
  /**
   * @var Routing Routing module.
   */
  private $routing;
  
  /**
   * @var Snippet Snippets module;
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
  public function toRoute($routeString) {
    $route = array(
      'snippet' => substr($routeString, 8),
      'parameters' => array()
    );
    return $route;
  }

  /**
   * {@inheritdoc}
   */
  public function fromRoute($route) {
    return $route['snippet'];
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
    $snippet = $this->snippets->getSnippet($route['snippet']);
    if (!isset($snippet))
      throw new InvalidRouteException(tr('Invalid controller: %1', $route['snippet']));
    $response = $snippet($route['parameters']);
    if (is_string($response))
      $response = new TextResponse(Http::OK, 'text', $response);
    if (!($response instanceof Response)) {
      throw new InvalidResponseException(tr(
        'An invalid response was returned from snippet: %1',
        $route['snippet']
      ));
    }
    return $response;
  }
}
