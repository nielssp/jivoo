<?php
// Extension
// Name         : TinyMCE
// Category     : JavaScript WYSIWYG editor
// Website      : http://tinymce.com
// Version      : 3.4.4 
// Dependencies : templates ext;jquery ext;jquery-ui 

class Tinymce extends ExtensionBase implements IEditor {
  
  private $format = NULL;
  
  protected function init() {
    $this->format = new HtmlFormat();
    
    $this->m->Templates->addScript(
      'tinymce',
      $this->getLink('js/jquery.tinymce.js'),
      array('jquery', 'jquery-ui')
    );
  }

  public function configure(Configuration $config) {
    $this->config = $config;
    $this->format->configure($config->getSubset('format'));
  }
  
  public function getFormat() {
    return $this->format;
  }
}
