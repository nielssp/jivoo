<?php
class RecordIndexRecordColumn extends RecordIndexColumn {
  public $recordField;
  public $action;

  public function __construct($field, $label = null, $primary = false, $recordField, $action = null) {
    parent::__construct($field, $label, $primary);
    $this->recordField = $recordField;
    $this->action = $action;
  }

  public function getValue(IBasicRecord $record) {
    $field = $this->field;
    $recordField = $this->recordField;
    return h($record->$field->$recordField);
  }
}