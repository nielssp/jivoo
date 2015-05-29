<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

use Jivoo\Models\IBasicRecord;

class RecordFilterVisitor extends FilterVisitor {

  private $record;
  private $model;
  private $primary;

  public function __construct($Filtering, IBasicRecord $record = null) {
    $this->primary = $Filtering->primary;
    $this->record = $record;
    if (isset($record))
      $this->model = $record->getModel();
  }
  
  public function setRecord(IBasicRecord $record) {
    $this->record = $record;
    $this->model = $record->getModel();
  }

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
  protected function visitNotTerm(NotTermNode $node) {
    return !$this->visit($node->child);
  }
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
