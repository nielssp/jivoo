<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering\Ast;

/**
 * A comparison operation.
 */
class ComparisonNode extends Node {
  /**
   * @var string Left side, a field name.
   */
  public $left = '';

  /**
   * @var string Comaparison operator.
   */
  public $comparison = '';

  /**
   * @var string Right side.
   */
  public $right = '';

  public function __construct($left, $comparison, $right) {
    $this->left = $left;
    $this->comparison = $comparison;
    $this->right = $right;
  }
}