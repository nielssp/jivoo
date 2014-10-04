<?php
class FormatHelper extends Helper {
  protected $modules = array('Content');

  protected $helpers = array('Form');
  
  public function set(IModel $model, $field, HtmlEncoder $encoder) {
    $this->m->Content->setEncoder($model, $field, $encoder);
  }
  
  public function encoder(IModel $model, $field) {
    return $this->m->Content->getEncoder($model, $field);
  }

  public function selectFormat($field) {
    $options = array_keys($this->m->Content->getFormats());
    $options = array_combine($options, $options);
    return $this->Form->selectOf($field . 'Format', $options);
  }

  public function formatOf(IRecord $record, $field) {
    $formatField = $field . 'Format';
    return $this->m->Content->getFormat($record->$formatField);
  }

  public function text(IRecord $record, $field) {
    $textField = $field . 'Text';
    return h($record->$textField);
  }

  public function html(IRecord $record, $field, $options = array()) {
    $encoder = $this->m->Content->getEncoder($record->getModel(), $field);
    $htmlField = $field . 'Html';
    return $encoder->encode($record->$htmlField, $options);
  }
}
