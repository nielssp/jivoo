<?php
class RecordIndexColumn {
  public $field;
  public $label;
  public $primary;

  public function __construct($field, $label = null, $primary = false) {
    $this->field = $field;
    $this->label = $label;
    $this->primary = $primary;
  }

  public function getLabel(IBasicModel $model) {
    if (!isset($this->label))
      $this->label = $model->getLabel($this->field);
    return $this->label;
  }

  public function getValue(IBasicRecord $record) {
    $field = $this->field;
    return h($record->$field);
  }
}