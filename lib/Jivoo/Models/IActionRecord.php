<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * Contains several links to actions.
 */
interface IActionRecord {
  /**
   * Get route to a named action.
   * @param string $action Action name.
   * @return array|ILinkable|string|null A route, see {@see Routing}.
   */
  public function action($action);
}