<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Core\LoadableModule;
use Jivoo\Models\Model;
use Jivoo\Models\BasicModel;
use Jivoo\Models\BasicRecord;
use Jivoo\ActiveModels\ActiveModel;
use Jivoo\Routing\InvalidRouteException;

/**
 * Content editing and presentation module.
 * @property-read ContentExtensions $extensions Collection of content extensions.
 */
class Content extends LoadableModule {
  /**
   * @var ContentFormat[] Formats.
   */
  private $formats = array();
  
  /**
   * @var HtmlEncoder[][] Associative array of model names and field encoders.
   */
  private $encoders = array();
  
  /**
   * @var Editor[][] Associative array of format names and list of editors.
   */
  private $editors = array();

  /**
   * @var Editor[][] Associative array of model names and field editors.
   */
  private $defaultEditors = array();
  
  /**
   * @var bool[][] Whether or not extensions are enabled on a model field.
   */
  private $extensionsEnabled = array();
  
  /**
   * @var ContentExtensions Collection of content extensions.
   */
  private $extensions;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->vendor->import('ezyang/htmlpurifier');

    $this->m->lazy('Helpers')->addHelper('Jivoo\Content\ContentHelper');
    
    $this->m->lazy('Helpers')->addHelper('Jivoo\Content\EditorHelper');
    $this->m->lazy('Helpers')->addHelper('Jivoo\Content\FormatHelper');
    
    $this->extensions = new ContentExtensions();
    
    $this->extensions->inline('link', array('route' => null), array($this, 'linkFunction'));
    $this->extensions->block('break', array(), array($this, 'breakFunction'));
    $this->extensions->block('page', array('name' => null), array($this, 'pageFunction'));
    $this->extensions->block('pagebreak', array(), array($this, 'pageBreakFunction'));
  }
  
  /**
   * Insert link for route.
   * @param array $params Content extension parameters.
   * @return string Link.
   */
  public function linkFunction($params) {
    try {
      return $this->m->Routing->getLink($params['route']);
    }
    catch (InvalidRouteException $e) {
      return 'invalid link';
    }
  }
  
  /**
   * Create a break between summary and full content.
   * @param array $params Content extension parameters.
   * @return string Break div.
   */
  public function breakFunction($params) {
    return '<div class="break"></div>';
  }

  /**
   * Create a page break.
   * @param array $params Content extension parameters.
   * @return string Page break div.
   */
  public function pageBreakFunction($params) {
    return '<div class="page-break"></div>';
  }

  /**
   * Name the current content page.
   * @param array $params Content extension parameters.
   * @return string Page name div.
   */
  public function pageFunction($params) {
    if (isset($params['name']))
      return '<div class="page-name" data-name="' . h($params['name']) . '"></div>';
    else
      return '<div class="page-name"></div>';
  }
  

  /**
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'extensions':
        return $this->$property;
    }
    return parent::__get($property);
  }
  
  /**
   * Enable content extensions on a field in a model.
   * @param Model $model A model.
   * @param string $field Field name.
   */
  public function enableExtensions(Model $model, $field) {
    $name = $model->getName();
    if (!isset($this->extensionsEnabled[$name]))
      $this->extensionsEnabled[$name] = array();
    $this->extensionsEnabled[$name][$field] = true;
  }
  
  /**
   * Disable content extensions on a field in a model.
   * @param Model $model A model.
   * @param string $field Field name.
   */
  public function disableExtensions(Model $model, $field) {
    $name = $model->getName();
    if (!isset($this->extensionsEnabled[$name]))
      return;
    if (!isset($this->extensionsEnabled[$name][$field]))
      return;
    unset($this->extensionsEnabled[$name][$field]);
  }
  
  /**
   * Get encoder for a field.
   * @param BasicModel $model A model.
   * @param string $field Field name.
   * @return HtmlEncoder Encoder.
   */
  public function getEncoder(BasicModel $model, $field) {
    $name = $model->getName();
    if (!isset($this->encoders[$name]))
      $this->encoders[$name] = array();
    if (!isset($this->encoders[$name][$field]))
      $this->encoders[$name][$field] = new HtmlEncoder();
    return $this->encoders[$name][$field];
  }

  /**
   * Get all formats.
   * @return ContentFormat[] Associative array of format names and format objects.
   */
  public function getFormats() {
    return $this->formats;
  }
  
  /**
   * Get a content format.
   * @param string $name Format name.
   * @return ContentFormat|null Format object or null if undefined.
   */
  public function getFormat($name) {
    if (isset($this->formats[$name]))
      return $this->formats[$name];
    return null;
  }

  /**
   * Add a content format.
   * @param ContentFormat $format Format object.
   */
  public function addFormat(ContentFormat $format) {
    $name = $format->getName();
    $this->formats[$name] = $format;
    $this->editors[$name] = array();
  }
  
  /**
   * Compile content of a field, i.e. convert to HTML, encode, and apply content
   * extensions if enabled. 
   * @param BasicRecord $record A record.
   * @param string $field Field name.
   * @return string Compiled and encoded content.
   */
  public function compile(BasicRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    $formatField = $field . 'Format';
    $format = $this->getFormat($record->$formatField);
    if (!isset($format))
      return $record->$field;
    $html = $format->toHtml($record->$field);
    if (!isset($this->extensionsEnabled[$name]))
      return $html;
    if (!isset($this->extensionsEnabled[$name][$field]))
      return $html;
    return $this->extensions->compile($html);
  }

  /**
   * Add an editor.
   * @param Editor $editor Editor object.
   * @throws \Exception If format used by editor is unknown.
   */
  public function addEditor(Editor $editor) {
    $format = $editor->getFormat();
    if (!isset($this->editors[$format]))
      throw new \Exception('Unknown format: ' . $format);
    $this->editors[$format][] = $editor;
  }
  
  /**
   * Get default editor for field.
   * @param BasicRecord $record A record.
   * @param string $field Field name.
   * @return Edtor|null An editor or null if no default.
   */
  public function getDefaultEditor(BasicRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    if (isset($this->defaultEditors[$name])) {
      if (isset($this->defaultEditors[$name][$field])) {
        return $this->defaultEditors[$name][$field];
      }
    }
    return null;
  }

  /**
   * Get editor for field.
   * @param BasicRecord $record A record.
   * @param string $field Field name.
   * @return Edtitor|null An editor or null if none available.
   */
  public function getEditor(BasicRecord $record, $field) {
    $model = $record->getModel();
    $name = $model->getName();
    $formatField = $field . 'Format';
    $format = $this->getFormat($record->$formatField);
    if (!isset($format))
      return null;
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
  
  /**
   * Set editor for field.
   * @param BasicModel $model A model.
   * @param string $field Field name.
   * @param Editor $editor An editor.
   */
  public function setEditor(ActiveModel $model, $field, Editor $editor) {
    $name = $model->getName();
    if (!isset($this->defaultEditors[$name]))
      $this->defaultEditors[$name] = array();
    $this->defaultEditors[$name][$field] = $editor;
    $filter = new ContentFilter($this, $field);
    $model->attachEventHandler('afterCreate', array($filter, 'afterCreate'));
    $model->attachEventHandler('beforeValidate', array($filter, 'beforeSave'));
  }
}
