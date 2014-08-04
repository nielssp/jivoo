<?php
// Extension
// Name : PeanutCMS example extension
// Dependencies : Templates Posts>=0.2.0 Extensions php;Mysql ext;Tinymce

class Test extends ExtensionBase {
  protected function init() {

    $this->m
      ->Templates
      ->insertHtml('test-output', 'body-bottom', 'div',
        array('style' => 'text-align:center;'), 'hello ');
  }
}
