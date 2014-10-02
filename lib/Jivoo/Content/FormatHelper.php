<?php
class FormatHelper extends Helper {
  protected $modules = array('Content');
  
  public function set(IModel $model, $field, HtmlEncoder $encoder) {
    $this->m->Content->setEncoder($model, $field, $encoder);
  }
  
  public function encoder(IModel $model, $field) {
    return $this->m->Content->getEncoder($model, $field);
  }

  public function html(IRecord $record, $field, $options = array()) {
    $html = $this->m->Content
      ->getFormat('html')
      ->toHtml($record->$field);
    $encoder = $this->m->Content->getEncoder($record->getModel(), $field);
    return $encoder->encode($html, $options);
  }
}
