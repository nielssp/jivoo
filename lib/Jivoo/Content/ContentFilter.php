<?php
class ContentFilter {

  private $field;
  private $format;
  
  public function __construct($field, IContentFormat $format) {
    $this->field = $field;
    $this->format = $format;
  }

  public function afterCreate(ActiveModelEvent $event) {
    $formatField = $this->field . 'Format';
    $event->record->$formatField = $this->format->getName();
  }

  public function beforeSave(ActiveModelEvent $event) {
    $field = $this->field;
    $content = $event->record->$field;
    $htmlField = $field . 'Html';
    $formatField = $field . 'Format';
    $textField = $field . 'Text';
    $event->record->$formatField = $this->format->getName();
    $event->record->$htmlField = $this->format->toHtml($event->record->$field);
    $event->record->$textField = $this->format->toText($event->record->$field);
  }
}
