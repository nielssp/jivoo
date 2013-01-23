<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : Templates Routes Editors
//                Assets ext;Jquery ext;JqueryUi 

class Tinymce extends ExtensionBase {

  private $encoder = null;
  private $controller = null;

  protected function init() {
    $this->load('TinymceEditor');
    $this->load('TinymceController');

    $this->controller = new TinymceController($this->m
      ->Routes, $this->config);
    $this->controller
      ->addExtension($this);
    $this->controller
      ->addRoute('tinymce/init.js', 'initJs');

    $this->controller
      ->addTemplatePath($this->p('templates'));

    $editor = new TinymceEditor($this);
    $this->m
      ->Editors
      ->TinymceEditor = $editor;

    $this->m
      ->Templates
      ->addScript('tinymce', $this->getAsset('jquery.tinymce.js'),
        array('jquery', 'jquery-ui'));
  }

  public function getScriptUrl() {
    return $this->getAsset('tiny_mce.js');
  }

  public function getStyleUrl() {
    return $this->getAsset('css/content.css');
  }

  public function insertScripts() {
    $this->m
      ->Templates
      ->insertScript('tinymce-init',
        $this->m
          ->Routes
          ->getLink(
            array('controller' => $this->controller, 'action' => 'initJs')),
        array('tinymce'));
  }

  public function setEncoder(Encoder $encoder) {
    $this->encoder = $encoder;
  }
}
