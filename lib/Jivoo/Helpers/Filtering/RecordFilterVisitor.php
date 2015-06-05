<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

use Jivoo\Models\IBasicRecord;
use Jivoo\Models\IBasicModel;
use Jivoo\Helpers\FilteringHelper;
use Jivoo\Helpers\Filtering\Ast\FilterNode;
use Jivoo\Helpers\Filtering\Ast\NotTermNode;
use Jivoo\Helpers\Filtering\Ast\ComparisonNode;
use Jivoo\Helpers\Filtering\Ast\StringNode;

/**
 * A visitor that applies a filter to a single record.
 * Returns a bool.
 */
class RecordFilterVisitor extends FilterVisitor {
  /**
   * @var IBasicRecord Record.
   */
  private $record;
  
  /**
   * @var IBasicModel Model.
   */
  private $model;
  
  /**
   * @var string[] Primary columns.
   */
  private $primary;

  /**
   * Construct record filter visitor.
   * @param FilteringHelper $Filtering Filtering helper.
   * @param IBasicRecord $record Record.
   */
  public function __construct(FilteringHelper $Filtering, IBasicRecord $record = null) {
    $this->primary = $Filtering->primary;
    $this->record = $record;
    if (isset($record))
      $this->model = $record->getModel();
  }
  
  /**
   * Set record to test.
   * @param IBasicRecord $record Record.
   */
  public function setRecord(IBasicRecord $record) {
    $this->record = $record;
    $this->model = $record->getModel();
  }

  /**
   * {@inheritdoc}
   */
  protected function visitFilter(FilterNode $node) {
    $result = null;
    foreach ($node->children as $child) {
      if ($result === null)
        $result = $this->visit($child);
      else if ($child->operator == 'or')
        $result = $result || $this->visit($child);
      else
        $result = $result && $this->visit($child);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function visitNotTerm(NotTermNode $node) {
    return !$this->visit($node->child);
  }

  /**
   * {@inheritdoc}
   */
  protected function visitComparison(ComparisonNode $node) {
    $field = $node->left;
    if (!$this->model->hasField($field))
      return false;
    $type = $this->model->getType($field);
    $right = $type->convert($node->right);
    switch ($node->comparison) {
      case '=':
        return $this->record->$field == $right;
      case '!=':
        return $this->record->$field != $right;
      case '<=':
        return $this->record->$field <= $right;
      case '>=':
        return $this->record->$field >= $right;
      case '>':
        return $this->record->$field > $right;
      case '<':
        return $this->record->$field < $right;
      case 'contains':
        return stripos($this->record->$field, $node->right) !== false;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function visitString(StringNode $node) {
    if (count($this->primary) == 0)
      return false;
    foreach ($this->primary as $column) {
      if (stripos($this->record->$column, $node->value) !== false)
        return true;
    }
    return false;
  }
}
