<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Control;

use Jivoo\Helpers\Helper;
use Jivoo\Routing\Response;
use Jivoo\Routing\NotFoundException;

/**
 * Find and embed snippets.
 */
class SnippetHelper extends Helper {
  /**
   * Get a snippet instance.
   * @param string $name Snippet class name.
   * @param bool $singleton Whether to use an existing instance instead of
   * creating a new one.
   * @return Snippet Snippet instance or null if not found.
   */
  public function getSnippet($name, $singleton = true) {
    return $this->m->Routing->dispatchers->snippet->getSnippet($name, $singleton);
  }

  /**
   * Get snippet instance.
   * @param string $snippet Snippet name.
   * @return Snippet Snippet instance.
   */
  public function __get($snippet) {
    return $this->getSnippet($snippet);
  }
  
  /**
   * Invoke a snippet.
   * @param string $snippet Snippet name.
   * @param array $parameters Parameters.
   * @return Response|string Response or content.
   */
  public function __call($snippet, $parameters) {
    try {
      $response = $this->__get($snippet)->__invoke($parameters);
      if ($response instanceof Response)
        return $response->body;
      return $response;
    }
    catch (NotFoundException $exception) {
      return tr('Not found');
    }
  }
  
  /**
   * Invoke snippet.
   * @param string $snippet Snippet.
   * @param mixed $parameters,... Parameters.
   * @return Response|string Response or content.
   */
  public function __invoke($snippet) {
    $parameters = func_get_args();
    array_shift($parameters);
    $response = $this->__call($snippet, $parameters);
    if ($response instanceof Response)
      return $response->body;
    return $response;
  }
}
