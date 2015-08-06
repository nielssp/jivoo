<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering\Ast;

/**
 * A filter node.
 */
class FilterNode extends Node {
  /**
   * @var Node[] Children.
   */
  public $children = array();

  /**
   * Construct filter node.
  */
  public function __construct() {
    $this->children = func_get_args();
  }
}