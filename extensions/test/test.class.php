<?php
// Extension
// Name : PeanutCMS example extension
// Dependencies : templates

class Test extends ExtensionBase {
  protected function init() {
    $this->config->set('test', 'test');
    $this->templates->insertHtml(
      'test-output',
      'body-bottom',
      'div',
      array('style' => 'text-align:center;'),
      'Hello, World!'
    );
  } 
}