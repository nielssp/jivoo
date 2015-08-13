<?php
use Jivoo\Content\ContentFormat;
use Jivoo\Extensions\ExtensionModule;
use Jivoo\Content\TextareaEditor;

class MarkdownFormat extends ExtensionModule implements ContentFormat {
  
  protected $modules = array('Content');
  
  protected function init() {
    $this->m->Content->addFormat($this);
    $this->m->Content->addEditor(new TextareaEditor('markdown'));
  }
  
  public function getName() {
    return 'markdown';
  }
  
  public function toHtml($text) {
    $Parsedown = new Parsedown();
    return $Parsedown->text($text);
  }
  
  public function toText($text) {
    $encoder = new HtmlEncoder();
    return $encoder->encode($this->toHtml($text));
  }
}
