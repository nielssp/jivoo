<?php
// Extension
// Name         : jQuery hotkeys plug-in
// Category     : JavaScript jQuery
// Website      : http://code.google.com/p/js-hotkeys/
// Version      : 0.7.9
// Dependencies : Templates Assets ext;Jquery

class JqueryHotkeys extends ExtensionBase {
  protected function init() {
    $this->m->Templates->addScript(
      'jquery-hotkeys',
      $this->getAsset('js/jquery.hotkeys-0.7.9-fix.js'),
      array('jquery')
    );
  }
}
