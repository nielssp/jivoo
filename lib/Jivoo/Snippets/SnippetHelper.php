<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Helpers\Helper;
use Jivoo\Routing\Response;
use Jivoo\Routing\NotFoundException;

/**
 * Find and embed snippets.
 */
class SnippetHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Snippets');
  
  /**
   * Get snippet instance.
   * @param string $snippet Snippet name.
   * @return ISnippet Snippet instance.
   */
  public function __get($snippet) {
    return $this->m->Snippets->getSnippet($snippet);
  }
  
  /**
   * Invoke a snippet.
   * @param string $snippet Snippet name.
   * @param array $parameters Parameters.
   * @return Response|string Response or content.
   */
  public function __call($snippet, $parameters) {
    try {
      return $this->m->Snippets->getSnippet($snippet)->__invoke($parameters);
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
    return $this->__call($snippet, $parameters);
  }
}