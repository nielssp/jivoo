<?php
class RecordFilterVisitor extends FilterVisitor {

  private $record;
  private $primary;

  public function __construct($Filtering, IBasicRecord $record) {
    $this->primary = $Filtering->primary;
    $this->record = $record;
  }
  
  public function setRecord(IBasicRecord $record) {
    $this->record = $record;
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
    /// @TODO check for existence of column. AND TYPE
    $field = $node->left;
    switch ($node->comparison) {
      case '=':
        return $this->record->$field == $node->right;
      case '!=':
        return $this->record->$field != $node->right;
      case '<=':
        return $this->record->$field <= $node->right;
      case '>=':
        return $this->record->$field >= $node->right;
      case '>':
        return $this->record->$field < $node->right;
      case '<':
        return $this->record->$field > $node->right;
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