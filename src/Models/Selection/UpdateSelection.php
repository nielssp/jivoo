<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Selection;

/**
 * An update selection.
 */
interface UpdateSelection extends BasicSelection {
  /**
   * @return array Associative array of field names and values
   */
  public function getData();
}
