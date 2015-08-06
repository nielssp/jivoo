<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

use Jivoo\Helpers\Filtering\Ast\FilterNode;
use Jivoo\Helpers\Filtering\Ast\NotTermNode;
use Jivoo\Helpers\Filtering\Ast\ComparisonNode;
use Jivoo\Helpers\Filtering\Ast\StringNode;
use Jivoo\Helpers\Filtering\Ast\Node;
use Jivoo\InvalidArgumentException;

/**
 * A visitor for abstract syntax trees produced by {@see FilterParser}.
 */
abstract class FilterVisitor {
  /**
   * Visit a filter node.
   * @param FilterNode $node A filter node.
   * @return mixed Output.
   */
  protected abstract function visitFilter(FilterNode $node);

  /**
   * Visit a not term node.
   * @param NotTermNode $node A not term node.
   * @return mixed Output.
   */
  protected abstract function visitNotTerm(NotTermNode $node);

  /**
   * Visit a comparison node.
   * @param ComparisonNode $node A comparison node.
   * @return mixed Output.
   */
  protected abstract function visitComparison(ComparisonNode $node);

  /**
   * Visit a string node.
   * @param StringNode $node A string node.
   * @return mixed Output.
   */
  protected abstract function visitString(StringNode $node);
  
  /**
   * Visit an AST node.
   * @param Node $node Node.
   * @throws InvalidArgumentException If node class is unknown.
   * @return mixed Output.
   */
  public function visit(Node $node) {
    if ($node instanceof FilterNode) {
      return $this->visitFilter($node);
    }
    if ($node instanceof NotTermNode) {
      return $this->visitNotTerm($node);
    }
    if ($node instanceof ComparisonNode) {
      return $this->visitComparison($node);
    }
    if ($node instanceof StringNode) {
      return $this->visitString($node);
    }
    throw new InvalidArgumentException('Unknown node: ' . get_class($node));
  }
}