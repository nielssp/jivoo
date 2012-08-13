<?php
// Extension
// Name         : jQuery hotkeys plug-in
// Category     : JavaScript jQuery
// Version      : 0.7.9
// Dependencies : Templates ext;Jquery

class JqueryHotkeys extends ExtensionBase {
  protected function init() {
    $this->m->Templates->addScript(
      'jquery-hotkeys',
      $this->getLink('js/jquery.hotkeys-0.7.9.min.js'),
      array('jquery')
    );
  } 
}
