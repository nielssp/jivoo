<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

use Jivoo\Models\Condition\Condition;
use Jivoo\Models\Condition\NotCondition;
use Jivoo\Models\IBasicModel;
use Jivoo\Helpers\FilteringHelper;
use Jivoo\Helpers\Filtering\Ast\FilterNode;
use Jivoo\Helpers\Filtering\Ast\NotTermNode;
use Jivoo\Helpers\Filtering\Ast\ComparisonNode;
use Jivoo\Helpers\Filtering\Ast\StringNode;
use Jivoo\Core\I18n;

/**
 * A visitor that applies a filter to a model and produces a {@see Condition} for
 * use with selections.
 */
class SelectionFilterVisitor extends FilterVisitor {
  /**
   * @var FilteringHelper Filtering helper.
   */
  private $Filtering;
  
  /**
   * @var string[] Priamry columns.
   */
  private $primary;
  
  /**
   * @var IBasicModel Model.
   */
  private $model;

  /**
   * Construct visitor.
   * @param FilteringHelper $Filtering Filtering helper.
   * @param IBasicModel $model Model.
   */
  public function __construct(FilteringHelper $Filtering, IBasicModel $model) {
    $this->Filtering = $Filtering;
    $this->primary = $this->Filtering->primary;
    $this->model = $model;
  }

  /**
   * {@inheritdoc}
   */
  protected function visitFilter(FilterNode $node) {
    if (count($node->children) == 0)
      return new Condition('false');
    $condition = new Condition();
    foreach ($node->children as $child) {
      $cond = $this->visit($child);
      if ($child->operator == 'or') {
        $condition->orWhere($cond);
      }
      else {
        $condition->andWhere($cond);
      }
    }
    return $condition;
  }

  /**
   * {@inheritdoc}
   */
  protected function visitNotTerm(NotTermNode $node) {
    return new NotCondition($this->visit($node->child));
  }

  /**
   * {@inheritdoc}
   */
  protected function visitComparison(ComparisonNode $node) {
    if (!$this->model->hasField($node->left))
      return new Condition('false');
    $type = $this->model->getType($node->left);
    $right = $type->convert($node->right);
    switch ($node->comparison) {
      case '=':
      case '!=':
      case '<=':
      case '>=':
      case '>':
      case '<':
        if ($type->isDate() or $type->isDateTime()) {
          $interval = I18n::stringToInterval($node->right);
          if (isset($interval)) {
            list($start, $end) = $interval;
            switch ($node->comparison) {
              case '=':
                $cond = new Condition('%m.%c >= %_', $this->model, $node->left, $type, $start);
                $cond->and('%m.%c <= %_', $this->model, $node->left, $type, $end);
                return $cond;
              case '!=':
                $cond = new Condition('%m.%c < %_', $this->model, $node->left, $type, $start);
                $cond->or('%m.%c > %_', $this->model, $node->left, $type, $end);
                return $cond;
              case '<':
              case '>=':
                $right = $start;
                break;
              default:
                $right = $end;
                break; 
            }
          }
        }
        return new Condition('%m.%c ' . $node->comparison . ' %_', $this->model, $node->left, $type, $right);
      case 'contains':
        return new Condition('%m.%c LIKE %s', $this->model, $node->left, '%' . Condition::escapeLike($right) . '%');
    }
    return new Condition('false');
  }

  /**
   * {@inheritdoc}
   */
  protected function visitString(StringNode $node) {
    if (count($this->primary) == 0)
      return new Condition('false');
    $condition = new Condition();
    foreach ($this->primary as $column) {
      $condition->or('%c LIKE %s', $column, '%' . Condition::escapeLike($node->value) . '%');
    }
    return $condition;
  }
}
