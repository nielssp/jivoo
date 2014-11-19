<?php
// Module
// Name           : Content
// Description    : Jivoo content editing and presentation
// Author         : apakoh.dk
// Dependencies   : Jivoo/Models Jivoo/ActiveModels

Lib::import('Jivoo/Content/Formats');

class Content extends LoadableModule {
  
  private $formats = array();
  
  private $encoders = array();
  
  private $editors = array();

  private $defaultEditors = array();
  
  private $extensionsEnabled = array();
  
  private $extensions;
  
  protected function init() {
    $this->extensions = new ContentExtensions();
    
    $this->addFormat(new HtmlFormat());
    $this->addFormat(new TextFormat());
    $this->addEditor(new TextareaEditor('html'));
    $this->addEditor(new TextareaEditor('text'));
    
    $this->extensions->add('link', array('route' => null), array($this, 'linkFunction'));
    $this->extensions->add('break', array(), array($this, 'breakFunction'));
    $this->extensions->add('page', array('name' => null), array($this, 'pageFunction'));
    $this->extensions->add('pagebreak', array(), array($this, 'pageBreakFunction'));
  }
  
  public function linkFunction($params) {
    try {
      return $this->m->Routing->getLink($params['route']);
    }
    catch (InvalidRouteException $e) {
      return 'invalid link';
    }
  }
  
  public function breakFunction($params) {
    return '<div class="break"></div>';
  }
  
  public function pageBreakFunction($params) {
    return '<div class="page-break"></div>';
  }
  
  public function pageFunction($params) {
    if (isset($params['name']))
      return '<div class="page-name" data-name="' . h($params['name']) . '"></div>';
    else
      return '<div class="page-name"></div>';
  }
  
  
  public function __get($property) {
    switch ($property) {
      case 'extensions':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  public function enableExtensions(IModel $model, $field) {
    $name = $model->getName();
    if (!isset($this->extensionsEnabled[$name]))
      $this->extensionsEnabled[$name] = array();
    $this->extensionsEnabled[$name][$field] = true;
  }
  
  public function disableExtensions(IModel $model, $field) {
    $name = $model->getName();
    if (!isset($this->extensionsEnabled[$name]))
      return;
    if (!isset($this->extensionsEnabled[$name][$field]))
      return;
    unset($this->extensionsEnabled[$name][$field]);
  }
  
  public function getEncoder(IModel $model, $field) {
    $name = $model->getName();
    if (!isset($this->encoders[$name]))
      $this->encoders[$name] = array();
    if (!isset($this->encoders[$name][$field]))
      $this->encoders[$name][$field] = new HtmlEncoder();
    return $this->encoders[$name][$field];
  }

  public function getFormats() {
    return $this->formats;
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
  
  public function compile(ActiveRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    $formatField = $field . 'Format';
    $format = $this->getFormat($record->$formatField);
    $html = $format->toHtml($record->$field);
    if (!isset($this->extensionsEnabled[$name]))
      return $html;
    if (!isset($this->extensionsEnabled[$name][$field]))
      return $html;
    return $this->extensions->compile($html);
  }

  public function addEditor(IEditor $editor) {
    $format = $editor->getFormat();
    if (!isset($this->editors[$format]))
      throw new Exception('Unknown format: ' . $format);
    $this->editors[$format][] = $editor;
  }
  
  public function getDefaultEditor(ActiveRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    if (isset($this->defaultEditors[$name])) {
      if (isset($this->defaultEditors[$name][$field])) {
        return $this->defaultEditors[$name][$field];
      }
    }
    return null;
  }

  public function getEditor(ActiveRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    $formatField = $field . 'Format';
    $format = $this->getFormat($record->$formatField);
    $formatName = $format->getName();
    $defaultEditor = $this->getDefaultEditor($record, $field);
    if ($defaultEditor->getFormat() == $formatName)
      return $defaultEditor;
    if (isset($this->editors[$formatName])) {
      $num = count($this->editors[$formatName]);
      if ($num > 0)
        return $this->editors[$formatName][0];
    }
    return null;
  }
  
  public function setEditor(ActiveModel $model, $field, IEditor $editor) {
    $name = $model->getName();
    if (!isset($this->defaultEditors[$name]))
      $this->defaultEditors[$name] = array();
    $this->defaultEditors[$name][$field] = $editor;
    $filter = new ContentFilter($this, $field);
    $model->attachEventHandler('afterCreate', array($filter, 'afterCreate'));
    $model->attachEventHandler('beforeValidate', array($filter, 'beforeSave'));
  }
}
