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
    $textField = $field . 'Text';
    $html = $this->Content->compile($event->record, $field);
    $textEncoder = new HtmlEncoder();
    $event->record->$htmlField = $html;
    $event->record->$textField = $textEncoder->encode($html); 
  }
}
