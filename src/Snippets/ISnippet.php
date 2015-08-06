<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Routing\Response;

/**
 * Snippet interface.
 */
interface ISnippet {
  /**
   * Execute snippet logic and produce response.
   * @param string[] $parameters Parameters.
   * @return Response|string A response object or content.
   */
  public function __invoke($parameters = array());
}