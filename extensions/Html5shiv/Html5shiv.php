<?php
// Extension
// Name         : html5shiv
// Category     : JavaScript
// Website      : https://code.google.com/p/html5shiv/
// Version      : 3.7.0
// Dependencies : Templates Assets

class Html5shiv extends ExtensionBase {
  protected function init() {
    $this->view->provide(
      'html5shiv.js',
      $this->getAsset('assets/js/html5shiv.js')
    );
  }
}
