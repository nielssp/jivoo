<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : templates ext;jquery ext;jquery-ui 

class Tinymce extends ExtensionBase {
  
  private $encoder = NULL;
  
  protected function init() {
    $this->load('TinymceEditor');
    $editor = new TinymceEditor($this);
    $this->m->Templates->addScript(
      'tinymce',
      $this->getLink('js/jquery.tinymce.js'),
      array('jquery', 'jquery-ui')
    );
  }

  public function setEncoder(Encoder $encoder) {
    $this->encoder = $encoder;
  }
}
