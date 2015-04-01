<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Content;

use Jivoo\ActiveModels\ActiveModelEvent;

/**
 * A filter for active models to apply content format, encoding, and extensions.
 */
class ContentFilter {
  /**
   * @var Content Content module.
   */
  private $Content;
  
  /**
   * @var string Field name.
   */
  private $field;
  
  /**
   * Construct filter.
   * @param Content $Content Content module.
   * @param string $field Field name.
   */
  public function __construct(Content $Content, $field) {
    $this->Content = $Content;
    $this->field = $field;
  }

  /**
   * Event handler for afterCreate-event. Sets format for new records.
   * @param ActiveModelEvent $event Event data.
   */
  public function afterCreate(ActiveModelEvent $event) {
    $defaultEditor = $this->Content->getDefaultEditor($event->record, $this->field);
    $formatField = $this->field . 'Format';
    if (isset($defaultEditor))
      $event->record->$formatField = $defaultEditor->getFormat();
    else
      $event->record->$formatField = 'html';
  }

  /**
   * Event handler for beforeSave-event. Compiles content and creates 
   * searchable text data.
   * @param ActiveModelEvent $event Event data.a
   */
  public function beforeSave(ActiveModelEvent $event) {
    $field = $this->field;
    $content = $event->record->$field;
    $htmlField = $field . 'Html';
    $textField = $field . 'Text';
    $html = $this->Content->compile($event->record, $field);
    $textEncoder = new HtmlEncoder();
    $event->record->$htmlField = $html;
    $event->record->$textField = $textEncoder->encode($html); 
  }
}
