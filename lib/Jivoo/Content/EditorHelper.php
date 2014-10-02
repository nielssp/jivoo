<?php
class EditorHelper extends Helper {
  protected $modules = array('Content');
  protected $helpers = array('Form');


  public function set(ActiveModel $model, $field, IEditor $editor) {
    $this->m->Content->setEditor($model, $field, $editor);
  }

  public function get($field, $options = array()) {
    $model = $this->Form->getModel();
    $editor = $this->m->Content->getEditor($model, $field);
    return $editor->field($this->Form, $field, $options);
  }
}
