<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering\Ast;

/**
 * An abstract syntax tree node for filters.
 */
abstract class Node {
  /**
   * @var string Preceding logical operator (either & or |).
   */
  public $operator = '';
}