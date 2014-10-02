<?php
class ContentFilter {
  
  private $field;
  private $editor;
  
  public function __construct($field, IEditor $editor) {
    $this->field = $field;
    $this->editor = $editor;
  }

  public function beforeSave(ActiveModelEvent $event) {
    $field = $this->field;
    $content = $event->record->$field;
    $event->record->$field = $this->editor->saveFilter($content);
  }
}