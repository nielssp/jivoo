<?php
class ContentFilter {

  private $field;
  private $formatName;
  private $format;
  
  public function __construct($field, $formatName, IContentFormat $format) {
    $this->field = $field;
    $this->formatName = $formatName;
    $this->format = $format;
  }

  public function afterCreate(ActiveModelEvent $event) {
    $formatField = $this->field . 'Format';
    $event->record->$formatField = $this->formatName;
  }

  public function beforeSave(ActiveModelEvent $event) {
    $field = $this->field;
    $content = $event->record->$field;
    $htmlField = $field . 'Html';
    $formatField = $field . 'Format';
    $event->record->$formatField = $this->formatName;
    $event->record->$htmlField = $this->format->toHtml($event->record->$field);
  }
}
