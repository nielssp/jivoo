<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\ActiveModels\ActiveModelMixin;
use Jivoo\ActiveModels\ActiveModelEvent;
use Jivoo\ActiveModels\ActiveRecord;
use Jivoo\Core\Assume;
use Jivoo\Core\Unicode;

/**
 * Mixin for automatically compiling content fields.
 */
class ContentMixin extends ActiveModelMixin {
  /**
   * {@inheritdoc}
   */
  protected $modules= array('Helpers');
  
  /**
   * {@inheritdoc}
   */
  protected $options = array(
    'fields' => array('content'),
    'text' => true,
    'html' => true,
    'format' => true,
    'html5' => true,
    'purifier' => array()
  );

  /**
   * {@inheritdoc}
   */
  protected $methods = array(
    'recordDisplay', 'recordEditor', 'recordHasBreak',
    'getDefaultFormat', 'setDefaultFormat',
    'addFilter', 'getPurifierConfig'
  );
  
  private $purifierConfigs = array();
  
  private $purifiers = array();
  
  private $filters = array();

  private $defaultFormat = 'html';

  /**
   * {@inheritdoc}
   */
  public function init() {
    $helper = $this->helper('Content');
    foreach ($this->options['fields'] as $field) {
      $helper->register($this->model, $field);
      $this->filters[$field] = array();
    }
  }
  
  private static function html5Config(\HTMLPurifier_Config $config) {
    $config->set('HTML.DefinitionID', 'jivoo.html5');
    $config->set('HTML.DefinitionRev', 1);
    $def = $config->maybeGetRawHTMLDefinition();
    if ($def) {
      $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
      $def->addElement('figcaption', 'Inline', 'Flow', 'Common');
    }
  }
  
  /**
   * Purify HTML for field.
   * @return string HTML.
   */
  public function recordDisplay(ActiveRecord $record, $field = 'content', $options = array()) {
    $purifier = $this->getPurifier($field);
    $htmlField = $field . 'Html';
    $content = $record->$htmlField;
    if (isset($options['break']) and $options['break']) {
      $sections = explode('<div class="break"></div>', $content);
      $content = $sections[0];
    }
    $html = $this->applyFilters($field, 'prerender', $content);
    if (isset($options['maxLength']) and Unicode::length($html) > $options['maxLength']) {
      $html = Unicode::slice($html, 0, $options['maxLength']);
      if (isset($options['append']))
        $html .= $options['append'];
    }
    return $purifier->purify($html);
  }
  
  /**
   * Render editor for field.
   * @return string HTML.
   */
  public function recordEditor(ActiveRecord $record, $field = 'content', $options = array()) {
    $formatField = $field . 'Format';
    $editor = $this->helper('Content')->getEditor($record->$formatField);
    if (!isset($editor))
      return tr('Unknown content format: "%1"', $record->$formatField);
    return $editor->field($this->helper('Form'), $field, $options);
  }

  public function recordHasBreak(ActiveRecord $record, $field = 'content') {
    $htmlField = $field . 'Html';
    $content = $record->$htmlField;
    $sections = explode('<div class="break"></div>', $content);
    return strpos($content, '<div class="break"></div>') !== false;
  }
  
  /**
   * Add a filter to a field.
   * @param string $field Field name.
   * @param string $stage Content stage: 'preprocess', 'postprocess', 'prerender'.
   * @param callable $callable Filter function, accepts a string and returns a
   * string.
   */
  public function addFilter($field, $stage, $callable) {
    Assume::hasKey($this->filters, $field);
    if (!isset($this->filters[$field][$stage]))
      $this->filters[$field][$stage] = array();
    $this->filters[$field][$stage][] = $callable;
  }
  
  /**
   * Apply a field's filters to a string.
   * @param string $field Field name.
   * @param string $stage Content stage: 'preprocess', 'postprocess', 'prerender'.
   * @param string $content Content.
   * @return Filtered content.
   */
  public function applyFilters($field, $stage, $content) {
    if (!isset($this->filters[$field]) or !isset($this->filters[$field][$stage]))
      return $content;
    foreach ($this->filters[$field][$stage] as $callable)
      $content = call_user_func($callable, $content);
    return $content;
  }
  
  /**
   * Get HTMLPurifier config for field.
   * @param string $field Field name.
   * @return \HTMLPurifier_Config Purifier configuration object.
   */
  public function getPurifierConfig($field = 'content') {
    if (!isset($this->purifierConfigs[$field])) {
      $this->purifierConfigs[$field] = \HTMLPurifier_Config::createDefault();
      $this->purifierConfigs[$field]->loadArray($this->options['purifier']);
    }
    return $this->purifierConfigs[$field];
  }
  
  public function getPurifier($field = 'content') {
    if (!isset($this->purifiers[$field])) {
      $config = $this->getPurifierConfig($field);
      if ($this->options['html5'] && !$config->isFinalized())
        self::html5Config($config);
      $this->purifiers[$field] = new \HTMLPurifier($config);
    }
    return $this->purifiers[$field];
  }

  public function getDefaultFormat() {
    return $this->defaultFormat;
  }

  public function setDefaultFormat($format) {
    $this->defaultFormat = $format;
  }

  /**
   * {@inheritdoc}
   */
  public function beforeSave(ActiveModelEvent $event) {
    if (!$this->options['html'] or !$this->options['text'])
      return;
    foreach ($this->options['fields'] as $field) {
      $content = $this->applyFilters($field, 'preprocess', $event->record->$field);
      $formatField = $field . 'Format';
      $editor = $this->helper('Content')->getEditor($event->record->$formatField);
      if (isset($editor)) {
        $html = $editor->toHtml($content);
      }
      else {
        $html = $content;
      }
      $html = $this->helper('Content')->extensions->compile($html);
      $html = $this->applyFilters($field, 'postprocess', $html);
      if ($this->options['html']) {
        $htmlField =  $field . 'Html';
        $event->record->$htmlField = $html; 
      }
      if ($this->options['text']) {
        $textField = $field . 'Text';
        $event->record->$textField = strip_tags($html); 
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function afterCreate(ActiveModelEvent $event) {
    if (!$this->options['format'])
      return;
    $helper = $this->helper('Content');
    foreach ($this->options['fields'] as $field) {
      $formatField = $field . 'Format';
      if (!$event->record->hasChanged($formatField))
        $event->record->$formatField = $this->defaultFormat;
    }
  }
}
