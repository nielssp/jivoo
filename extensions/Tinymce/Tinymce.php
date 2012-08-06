<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : templates routes editors ext;jquery ext;jquery-ui 

class Tinymce extends ExtensionBase {
  
  private $encoder = NULL;
  private $controller = NULL;
  
  protected function init() {
    $this->load('TinymceEditor');
    $this->load('TinymceController');

    $this->controller = new TinymceController($this->m->Templates, $this->m->Routes);
    $this->controller->addExtension($this);
    $this->controller->addRoute('tinymce/init.js', 'init');

    $this->controller->addTemplatePath($this->getPath('templates'));

    $editor = new TinymceEditor($this);
    $this->m->Editors->TinymceEditor = $editor;

    $this->m->Templates->addScript(
      'tinymce',
      $this->getLink('jquery.tinymce.js'),
      array('jquery', 'jquery-ui')
    );
  }

  public function getScriptUrl() {
    return $this->getLink('tiny_mce.js');
  }

  public function insertScripts() {
    $this->m->Templates->insertScript(
      'tinymce-init',
      $this->m->Routes->getLink(array(
        'controller' => $this->controller,
        'action' => 'init'
      )),
      array('tinymce')
    );
  }

  public function setEncoder(Encoder $encoder) {
    $this->encoder = $encoder;
  }
}
