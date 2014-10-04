<?php
class EditorHelper extends Helper {
  protected $modules = array('Content');
  protected $helpers = array('Form', 'Format');


  public function set(ActiveModel $model, $field, IEditor $editor) {
    $this->m->Content->setEditor($model, $field, $editor);
  }

  public function get($field, $options = array()) {
    $model = $this->Form->getModel();
    $record = $this->Form->getRecord();
    $editor = $this->m->Content->getEditor($model, $field);
    $format = $this->Format->formatOf($record, $field);
    if ($format != $editor->getFormat()) {
      // TODO convert / change editor etc...
      return 'Error: Editor does not support format: ' . $format;
    }
    return $editor->field($this->Form, $field, $options);
  }
}
