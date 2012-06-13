<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : templates ext;jquery ext;jquery-ui

class Tinymce extends ExtensionBase {
  protected function init() {
    $this->m->Templates->addScript(
      'tinymce',
      $this->getLink('js/jquery.tinymce.js'),
      array('jquery', 'jquery-ui')
    );
  } 
}
