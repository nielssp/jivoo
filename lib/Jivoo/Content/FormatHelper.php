<?php
class FormatHelper extends Helper {
  protected $modules = array('Content');
  
  public function set(IModel $model, $field, HtmlEncoder $encoder) {
    $this->m->Content->setEncoder($model, $field, $encoder);
  }
  
  public function encoder(IModel $model, $field) {
    return $this->m->Content->getEncoder($model, $field);
  }

  public function formatOf(IRecord $record, $field) {
    $formatField = $field . 'Format';
    return $this->m->Content->getFormat($record->$formatField);
  }

  public function text(IRecord $record, $field) {

  }

  public function html(IRecord $record, $field, $options = array()) {
    $encoder = $this->m->Content->getEncoder($record->getModel(), $field);
    $htmlField = $field . 'Html';
    return $encoder->encode($record->$htmlField, $options);
  }
}
