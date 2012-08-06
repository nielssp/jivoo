<?php

class DummyEditor implements IEditor {
  protected $editor = NULL;
  
  public function __construct(IEditor $editor) {
    $this->editor = $editor;
  }

  public function setEditor(IEditor $editor) {
    $this->editor = $editor;
  }

  public function init() {
    $this->editor->init();
  }

  public function getFormat() {
    return $this->editor->getFormat();
  }

  public function field(FormHelper $Form, $field, $options = array()) {
    return $this->editor->field($Form, $field, $options);
  }
}
