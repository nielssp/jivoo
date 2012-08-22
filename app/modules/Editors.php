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
    $this->editors[$editorName] = $editor;
  }

  public function getEditor($editor, Configuration $config = null) {
    $name = $editor;
    if ($editor instanceof Configuration) {
      $name = $editor['name'];
      if (isset($editor['config'])) {
        $config = $editor['config'];
      }
    }
    if (!is_string($name)) {
      return $this->HtmlEditor;
    }
    if (!isset($this->editors[$name])) {
      $this->editors[$name] = $this->HtmlEditor;
    }
    return $this->editors[$name]->init($config);
  }
}
