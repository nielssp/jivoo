<?php
class MarkdownFormat extends ExtensionModule implements IContentFormat {
  
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
