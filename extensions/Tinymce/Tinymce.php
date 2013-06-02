<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : Templates Routing Controllers Editors
//                Assets ext;Jquery ext;JqueryUi 

class Tinymce extends ExtensionBase {

  private $encoder = null;
  private $controller = null;

  protected function init() {
    $this->load('TinymceEditor');
    $this->load('controllers/TinymceController');
    $this->load('helpers/TinymceHelper');

    $this->controller = new TinymceController($this->m->Routing, $this->config);
    $this->controller->addHelper(new TinymceHelper(
      $this->getAsset('tiny_mce.js'),
      $this->getAsset('css/content.css')
    ));
    $this->controller->addRoute('tinymce/init.js', 'initJs');

    $this->controller->addTemplatePath($this->p('templates'));
    
    $this->m->Controllers->addController($this->controller);

    $editor = new TinymceEditor($this);
    $this->m->Editors->TinymceEditor = $editor;

    $this->m->Templates
      ->addScript('tinymce', $this->getAsset('jquery.tinymce.js'),
        array('jquery', 'jquery-ui')
      );
  }

  public function insertScripts() {
    $this->m->Templates
      ->insertScript('tinymce-init',
        $this->m->Routing
          ->getLink(
            array('controller' => $this->controller, 'action' => 'initJs')
          ), array('tinymce')
      );
  }

  public function setEncoder(Encoder $encoder) {
    $this->encoder = $encoder;
  }
}
