<?php
// Extension
// Name         : Respond
// Category     : JavaScript
// Website      : https://github.com/scottjehl/Respond
// Version      : 1.4.2
// Dependencies : Templates Assets

class Respond extends ExtensionBase {
  protected function init() {
    $this->view->provide(
      'respond.js',
      $this->getAsset('assets/js/respond.min.js')
    );
  }
}
