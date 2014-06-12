<?php
class RecordIndexDateColumn extends RecordIndexColumn {

  public function getValue(IBasicRecord $record) {
    $field = $this->field;
    return h(I18n::longDate($record->$field));
  }
}