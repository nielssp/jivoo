<?php
// Module
// Name           : Content
// Description    : Jivoo content editing and presentation
// Author         : apakoh.dk
// Dependencies   : Jivoo/Models Jivoo/ActiveModels

Lib::import('Jivoo/Content/Formats');
Lib::import('Jivoo/Content/Editors');

class Content extends LoadableModule {
  
  private $formats = array();
  
  private $encoders = array();
  
  private $editors = array();
  
  protected function init() {
    $this->setFormat('html', new HtmlFormat());
    $this->setFormat('text', new TextFormat());
  }
  
  
  public function getEncoder(IModel $model, $field) {
    $name = $model->getName();
    if (!isset($this->encoders[$name]))
      $this->encoders[$name] = array();
    if (!isset($this->encoders[$name][$field]))
      $this->encoders[$name][$field] = new HtmlEncoder();
    return $this->encoders[$name][$field];
  }
  
  public function getFormat($name) {
    return $this->formats[$name];
  }
  
  public function setFormat($name, IContentFormat $format) {
    $this->formats[$name] = $format;
  }
  
  public function getEditor(ActiveModel $model, $field) {
    $name = $model->getName();
    if (!isset($this->editors[$name]))
      $this->editors[$name] = array();
    if (!isset($this->editors[$name][$field]))
      throw new Exception('Editor not set for field "' . $field . '" in model ' . $model->getName());
    return $this->editors[$name][$field];
  }
  
  public function setEditor(ActiveModel $model, $field, IEditor $editor) {
    $name = $model->getName();
    if (!isset($this->editors[$name]))
      $this->editors[$name] = array();
    $filter = new ContentFilter($field, $editor);
    $model->attachEventHandler('beforeSave', array($filter, 'beforeSave'));
    $this->editors[$name][$field] = $editor;
  }
}
