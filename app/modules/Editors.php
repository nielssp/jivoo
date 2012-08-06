<?php
// Module
// Name           : Editors
// Version        : 0.3.1
// Description    : The PeanutCMS editor system
// Author         : PeanutCMS

class Editors extends ModuleBase {
  private $editors = array();

  protected function init() {
    $this->HtmlEditor = new HtmlEditor();
    $this->TextEditor = new TextEditor();
  }

  public function __get($editorName) {
    return $this->getEditor($editorName);
  }

  public function __set($editorName, $editor) {
    $this->setEditor($editorName, $editor);
  }

  public function setEditor($editorName, IEditor $editor) {
    if (isset($this->editors[$editorName]) AND $this->editors[$editorName] instanceof DummyEditor) {
      $this->editors[$editorName]->setEditor($editor);
    }
    else {
      $this->editors[$editorName] = $editor;
    }
  }

  public function getEditor($editorName) {
    if (!isset($this->editors[$editorName])) {
      $this->editors[$editorName] = new DummyEditor($this->HtmlEditor);
    }
    return $this->editors[$editorName];
  }
}
