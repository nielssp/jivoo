<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\ActiveModels\ActiveModelMixin;
use Jivoo\ActiveModels\ActiveModelEvent;
use Jivoo\ActiveModels\ActiveRecord;

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
  );

  /**
   * {@inheritdoc}
   */
  protected $methods = array('recordDisplay');

  /**
   * {@inheritdoc}
   */
  public function init() {
    $helper = $this->helper('Content');
    foreach ($this->options['fields'] as $field)
      $helper->register($this->model, $field);
  }
  
  /**
   * Purify HTML for field.
   * @return string HTML.
   */
  public function recordDisplay(ActiveRecord $record, $field = 'content') {
    return $this->helper('Content')->purify($record, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function beforeSave(ActiveModelEvent $event) {
    if (!$this->options['html'] or !$this->options['text'])
      return;
    foreach ($this->options['fields'] as $field) {
      $content = $event->record->$field;
      $formatField = $field . 'Format';
      $format = $this->helper('Content')->getFormat($event->record->$formatField);
      if (isset($format)) {
        $html = $format->toHtml($content);
      }
      else {
        $html = $content;
      }
      $html = $this->helper('Content')->extensions->compile($html);
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
      $editor = $helper->getEditor($event->record->getModel(), $field);
      $event->record->$formatField = get_class($editor->getFormat());
    }
  }
}
