<?php
// Module
// Name           : Editors
// Description    : The Jivoo editor system
// Author         : apakoh.dk

/**
 * Editors module. Each editor can be accessed as a property.
 * @package Jivoo\Editors
 * @property HtmlEditor $HtmlEditor A HTML editor
 * @property TextEditor $TextEditor A simpler text editor
 */
class Editors extends ModuleBase {
  /**
   * @var array Associative array of editor names and editor objects
   */
  private $editors = array();

  protected function init() {
    $this->HtmlEditor = new HtmlEditor();
    $this->TextEditor = new TextEditor();
  }

  /**
   * Get editor instance
   * @param string $editorName Name of editor
   * @return IEditor Editor object
   */
  public function __get($editorName) {
    return $this->getEditor($editorName);
  }

  /**
   * Add/set and editor instance
   * @param string $editorName Name of editor
   * @param IEditor $editor Editor object
   */
  public function __set($editorName, $editor) {
    $this->setEditor($editorName, $editor);
  }

  /**
   * Add/set and editor instance
   * @param string $editorName Name of editor
   * @param IEditor $editor Editor object
   */
  public function setEditor($editorName, IEditor $editor) {
    $this->editors[$editorName] = $editor;
  }

  /**
   * Get editor instance. If the first parameter is an instance of
   * {@see AppConfig}, the keys, 'name' and 'config', of that configuration will
   * be accessed to determine which editor to return.
   * @param string|AppConfig $editor Name of editor or configuration
   * @param AppConfig $config Optional configuration to initialise editor with
   * @return IEditor Editor object
   */
  public function getEditor($editor, AppConfig $config = null) {
    $name = $editor;
    if ($editor instanceof AppConfig) {
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
