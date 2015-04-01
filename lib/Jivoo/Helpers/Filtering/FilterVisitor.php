<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

abstract class FilterVisitor {
  protected abstract function visitFilter(FilterNode $node);
  protected abstract function visitNotTerm(NotTermNode $node);
  protected abstract function visitComparison(ComparisonNode $node);
  protected abstract function visitString(StringNode $node);
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
    throw new \Exception('Unknown node: ' . get_class($node));
  }
}