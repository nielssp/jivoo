<?php
class ContentFilter {

  private $Content;
  private $field;
  
  public function __construct(Content $Content, $field) {
    $this->Content = $Content;
    $this->field = $field;
  }

  public function afterCreate(ActiveModelEvent $event) {
    $defaultEditor = $this->Content->getDefaultEditor($event->record, $this->field);
    $formatField = $this->field . 'Format';
    if (isset($defaultEditor))
      $event->record->$formatField = $defaultEditor->getFormat();
    else
      $event->record->$formatField = 'html';
  }

  public function beforeSave(ActiveModelEvent $event) {
    $field = $this->field;
    $content = $event->record->$field;
    $htmlField = $field . 'Html';
    $formatField = $field . 'Format';
    $textField = $field . 'Text';
    $format = $this->Content->getFormat($event->record->$formatField);
    $event->record->$htmlField = $format->toHtml($content);
    $event->record->$textField = $format->toText($content);
  }
}
