<?php
// Extension
// Name : PeanutCMS example extension
// Dependencies : templates posts>=0.2.0 extensions php;mysql ext;Tinymce

class Test extends ExtensionBase {
  protected function init() {
    
    $this->m->Templates->insertHtml(
      'test-output',
      'body-bottom',
      'div',
      array('style' => 'text-align:center;'),
      'hello '
    );
  } 
}
