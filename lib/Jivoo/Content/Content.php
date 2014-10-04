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

  private $defaultEditors = array();
  
  protected function init() {
    $this->addFormat(new HtmlFormat());
    $this->addFormat(new TextFormat());
    $this->addFormat(new AltHtmlFormat());
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
    if (isset($this->formats[$name]))
      return $this->formats[$name];
    return null;
  }

  public function addFormat(IContentFormat $format) {
    $name = $format->getName();
    $this->formats[$name] = $format;
    $this->editors[$name] = array();
  }

  public function addEditor(IEditor $editor) {
    $format = $editor->getFormat();
    if (!isset($this->editors[$format]))
      throw new Exception('Unknown format: ' . $format);
    $this->editors[$name][] = $editor;
  }

  public function getEditor(ActiveRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    $formatField = $field . 'Format';
    $format = $this->getFormat($record->$formatField);
    if (!isset($this->editors[$name]))
      $this->editors[$name] = array();
    if (!isset($this->editors[$name][$field]))
      throw new Exception('Editor not set for field "' . $field . '" in model ' . $model->getName());
    return $this->editors[$name][$field];
  }
  
  public function setEditor(ActiveModel $model, $field, IEditor $editor) {
    $name = $model->getName();
    $this->defaultEditors[$name] = $editor;
    $format = $editor->getFormat();
    $filter = new ContentFilter($field, $this->getFormat($format));
    $model->attachEventHandler('afterCreate', array($filter, 'afterCreate'));
    $model->attachEventHandler('beforeValidate', array($filter, 'beforeSave'));
    $this->editors[$name][$field] = $editor;
  }
}
