<?php
// Extension
// Name         : Highlight.js
// Category     : JavaScript
// Website      : https://highlightjs.org
// Version      : 8.2
// Dependencies : Templates Assets

class Highlight extends ExtensionModule {
  protected function init() {
    $this->view->provide(
      'highlight.js',
      $this->getAsset('highlight.pack.js')
    );
    $this->view->provide(
      'highlight-init.js',
      $this->getAsset('highlight-init.js')
    );
    $this->view->provide(
      'highlight-style.css',
      $this->getAsset('styles/solarized_dark.css')
    );
    $this->view->import('highlight.js', 'highlight-init.js', 'highlight-style.css');
  }
}
