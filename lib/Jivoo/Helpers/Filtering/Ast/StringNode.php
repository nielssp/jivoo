<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering\Ast;

/**
 * A string literal.
 */
class StringNode extends Node {
  /**
   * @var string Value of string.
   */
  public $value = '';

  /**
   * Construc string literal.
   * @param string $value Value of string.
   */
  public function __construct($value) {
    $this->value = $value;
  }
}