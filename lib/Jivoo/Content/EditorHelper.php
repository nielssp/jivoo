<?php
class EditorHelper extends Helper {
  protected $modules = array('Content');
  protected $helpers = array('Form', 'Format');


  public function set(ActiveModel $model, $field, IEditor $editor) {
    $this->m->Content->setEditor($model, $field, $editor);
  }

  public function get($field, $options = array()) {
    $record = $this->Form->getRecord();
    $editor = $this->m->Content->getEditor($record, $field);
    if (!isset($editor)) {
      // TODO convert / change editor etc...
      return 'Error: No editor available for format: ' . $this->Format->formatOf($record, $field)->getName();
    }
    return $editor->field($this->Form, $field, $options);
  }
}
