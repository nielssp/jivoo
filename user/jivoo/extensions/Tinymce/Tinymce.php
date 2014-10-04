<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : Templates Routing Controllers Controllers 
//                Assets ext;Jquery ext;JqueryUi 

class Tinymce extends ExtensionBase {

  private $encoder = null;
  private $controller = null;

  protected function init() {
    $this->load('TinymceEditor');
    $this->load('controllers/TinymceController');
    $this->load('helpers/TinymceHelper');

    $this->controller = new TinymceController($this->app);
    $this->controller->Tinymce = $this;
    $this->controller->addRoute('tinymce/init.js', 'initJs');

    $this->view->addTemplateDir($this->p('templates'));
    
    $this->m->Controllers->addController($this->controller);

    $editor = new TinymceEditor($this);
    $this->m->Content->addEditor($editor);

    $this->view->provide(
      'jquery-tinymce.js',
      $this->getAsset('jquery.tinymce.js'),
      array('jquery.js', 'jquery-ui.js')
    );
    $this->view->provide(
      'tinymce-init.js',
      $this->m->Routing->getLink(array(
        'controller' => $this->controller,
        'action' => 'initJs'
      )),
      array('jquery-tinymce.js')
    );
  }

  public function insertScripts() {
    $this->view->script('tinymce-init.js');
  }

  public function setEncoder(Encoder $encoder) {
    $this->encoder = $encoder;
  }
}
