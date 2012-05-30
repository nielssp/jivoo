<?php
// Extension
// Name : PeanutCMS example extension
// Dependencies : templates posts>=0.2.0 extensions ext;test php;mysql

class Test extends ExtensionBase {
  protected function init() {
    
    $this->templates->insertHtml(
      'test-output',
      'body-bottom',
      'div',
      array('style' => 'text-align:center;'),
      'Hello, World!'
    );
  } 
}