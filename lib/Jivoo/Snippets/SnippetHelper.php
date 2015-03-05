<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Helpers\Helper;
use Jivoo\Routing\Response;

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
   * @return ISnippet Snippet instance.
   */
  public function __get($snippet) {
    return $this->m->Snippets->getSnippet($snippet);
  }
  
  /**
   * Invoke a snippet.
   * @return Response|string Response or content.
   */
  public function __call($snippet, $parameters) {
    return $this->m->Snippets->getSnippet($snippet)->__invoke($parameters);
  }
}