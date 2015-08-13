<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\Helpers\Helper;
use Jivoo\Models\Model;
use Jivoo\Models\BasicRecord;
use Jivoo\Routing\InvalidRouteException;

/**
 * Content format helper.
 */
class FormatHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Content');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form');
  
  /**
   * @var bool If content contains break.
   */
  private $hasBreak = false;
  
  /**
   * Set encoder of field.
   * @param Model $model A model.
   * @param String $field Field name.
   * @param HtmlEncoder $encoder Encoder.
   */
  public function set(Model $model, $field, HtmlEncoder $encoder) {
    $this->m->Content->setEncoder($model, $field, $encoder);
  }
  
  /**
   * Get encoder of field.
   * @param Model $model A model.
   * @param string $field Field name.
   * @return HtmLEncoder An encoder.
   */
  public function encoder(Model $model, $field) {
    return $this->m->Content->getEncoder($model, $field);
  }

  /**
   * Create select-element of available content formats.
   * @param string $field Field name.
   * @return string Select element HTML.
   */
  public function selectFormat($field) {
    $options = array_keys($this->m->Content->getFormats());
    $options = array_combine($options, $options);
    return $this->Form->selectOf($field . 'Format', $options);
  }

  /**
   * Get format for field.
   * @param BasicRecord $record A record.
   * @param string $field Field name.
   * @return ContentFormat|null Format object if available, otherwise null.
   */
  public function formatOf(BasicRecord $record, $field) {
    $formatField = $field . 'Format';
    return $this->m->Content->getFormat($record->$formatField);
  }
  
  /**
   * Enable content extensions on field.
   * @param Model $model A model.
   * @param string $field Field name.
   */
  public function enableExtensions(Model $model, $field) {
   $this->m->Content->enableExtensions($model, $field);
  }

  /**
   * Get cleartext content of field.
   * @param BasicRecord $record A record.
   * @param string $field Field name.
   * @return string Text content.
   */
  public function text(BasicRecord $record, $field) {
    $textField = $field . 'Text';
    return h($record->$textField);
  }

  /**
   * Get HTML content of field.
   * @param BasicRecord $record A record.
   * @param string $field Field name.
   * @param array $options Associative array of options for encoder, see
   * {@see HtmlEncoder::encode}.
   * @return string HTML encoded content.
   */
  public function html(BasicRecord $record, $field, $options = array()) {
    $encoder = $this->m->Content->getEncoder($record->getModel(), $field);
    $htmlField = $field . 'Html';
    $content = $record->$htmlField;
    $this->hasBreak = false;
    if (!isset($options['full']) or !$options['full']) {
      $sections = explode('<div class="break"></div>', $content);
      if (count($sections) > 1)
        $this->hasBreak = true;
      $content = $sections[0];
    }
    $content = $encoder->encode($content, $options);
    // TODO temporary jivoo-link replacer
    $routing = $this->m->Routing;
    $content = preg_replace_callback('/\bjivoo:([a-zA-Z0-9_]+:[-a-zA-Z0-9_\.~\\\\:\[\]?&+%\/=]+)/', function($matches) use ($routing){
      try {
        return $routing->getLink($matches[1]);
      }
      catch (InvalidRouteException $e) {
        return '#invalid-link';
      }
    }, $content);
    return $content;
  }
  
  /**
   * Whether or not content previously output with {@see html} has a break.
   * @return boolean True if break, false otherwise.
   */
  public function hasBreak() {
    return $this->hasBreak;
  }
}
