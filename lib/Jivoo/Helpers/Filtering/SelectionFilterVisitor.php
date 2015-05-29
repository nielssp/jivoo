<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

use Jivoo\Models\Condition\Condition;
use Jivoo\Models\Condition\NotCondition;
use Jivoo\Models\IBasicModel;

class SelectionFilterVisitor extends FilterVisitor {

  private $Filtering;
  private $primary;
  
  private $model;

  public function __construct($Filtering, IBasicModel $model) {
    $this->Filtering = $Filtering;
    $this->primary = $this->Filtering->primary;
    $this->model = $model;
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
    if (!$this->model->hasField($node->left))
      return new Condition('false');
    $type = $this->model->getType($node->left);
    $right = $type->convert($node->right);
    $placeholder = $type->placeholder;
    switch ($node->comparison) {
      case '=':
      case '!=':
      case '<=':
      case '>=':
      case '>':
      case '<':
        return new Condition($node->left . ' ' . $node->comparison . ' ' . $placeholder, $right);
      case 'contains':
        return new Condition($node->left . ' LIKE %s', '%' . Condition::escapeLike($right) . '%');
    }
    return new Condition('false');
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