<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Routing;

/**
 * An object that can be used in place of a route, see {@see Routing}.
 */
interface Linkable {
  /**
   * Get a route.
   * @return string|array|Linkable|null A route, see {@see Routing}.
   */
  public function getRoute();
}
