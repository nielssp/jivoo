<?php
// Extension
// Name         : jQuery UI
// Category     : JavaScript jQuery
// Website      : http://jqueryui.com
// Version      : 1.8.17
// Dependencies : templates ext;jquery>=1.3.2

class JqueryUi extends ExtensionBase {
  private $theme;

  protected function init() {
    if (!$this->config->exists('theme')) {
      $this->config->set('theme', 'arachis');
    }
    $this->theme = $this->config->get('theme');
    $this->templates->addScript(
      'jquery-ui',
      $this->getLink('js/jquery-ui-1.8.17.custom.min.js'),
      array('jquery', 'jquery-ui-css')
    );
    $this->templates->addStyle(
      'jquery-ui-css',
      $this->getLink('css/' . $this->theme . '/jquery-ui-1.8.17.custom.css')
    );
  } 
}
