<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering\Ast;

/**
 * A negated node.
 */
class NotTermNode extends Node {
  /**
   * @var Node Child.
   */
  public $child;

  /**
   * Construct negation node.
   * @param Node $child Child.
   */
  public function __construct(Node $child) {
    $this->child = $child;
  }
}