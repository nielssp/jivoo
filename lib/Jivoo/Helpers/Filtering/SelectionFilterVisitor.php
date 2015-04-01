<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

class SelectionFilterVisitor extends FilterVisitor {

  private $Filtering;
  private $primary;

  public function __construct($Filtering) {
    $this->Filtering = $Filtering;
    $this->primary = $this->Filtering->primary;
  }

  protected function visitFilter(FilterNode $node) {
    $condition = new Condition();
    foreach ($node->children as $child) {
      if ($child->operator == 'or') {
        $condition->orWhere($this->visit($child));
      }
      else {
        $condition->andWhere($this->visit($child));
      }
    }
    return $condition;
  }
  protected function visitNotTerm(NotTermNode $node) {
    return new NotCondition($this->visit($node->child));
  }
  protected function visitComparison(ComparisonNode $node) {
    /// @TODO check for existence of column. AND TYPE
    switch ($node->comparison) {
      case '=':
      case '!=':
      case '<=':
      case '>=':
      case '>':
      case '<':
        return new Condition($node->left . ' ' . $node->comparison . ' ?', $node->right);
      case 'contains':
        return new Condition($node->left . ' LIKE %s', '%' . $node->right . '%');
    }
  }
  protected function visitString(StringNode $node) {
    if (count($this->primary) == 0)
      return new Condition('false');
    $condition = new Condition();
    foreach ($this->primary as $column) {
      $condition->or($column . ' LIKE %s', '%' . $node->value . '%');
    }
    return $condition;
  }
}