<?php
use Jivoo\Extensions\ExtensionModule;
use Jivoo\Content\TextareaEditor;

class MarkdownFormat extends ExtensionModule {
  
  protected $helpers = array('Content');
  
  protected function init() {
    $this->helper('Content')->addEditor(new TextareaEditor('markdown', array($this, 'toHtml')));
  }
  
  public function toHtml($text) {
    $Parsedown = new Parsedown();
    return $Parsedown->text($text);
  }
}
