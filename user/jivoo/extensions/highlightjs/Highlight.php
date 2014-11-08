<?php

class Highlight extends ExtensionModule {
  protected function init() {
    $this->config->defaults = array(
      'theme' => 'solarized_dark'
    );
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
      $this->getAsset('styles/' . $this->config['theme'] . '.css')
    );
    $this->view->import('highlight.js', 'highlight-init.js', 'highlight-style.css');
  }
}
