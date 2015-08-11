<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;

/**
 * Application development control panel.
 */
class ControlPanel extends ConsoleSnippet {
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->view->data->title = tr('Control panel');
    $this->view->data->schemas = $this->findSchemas();
    $this->view->data->models = $this->findModels();
    $this->view->data->controllers = $this->findControllers();
    $this->view->data->snippets = $this->findSnippets();
    return parent::before();
  }
  
  private function findSchemas() {
    if (!is_dir($this->p('app/Schemas')))
      return array();
    $dir = new \DirectoryIterator($this->p('app/Schemas'));
    $schemas = array();
    foreach ($dir as $file) {
      if (!$file->isDot()) {
        if (preg_match('/^(.+)\.php$/', $file->getFilename(), $matches) === 1) {
          $class = $this->app->n('Schemas\\' . $matches[1]);
          $object = new $class();
          $schemas[] = $object;
        }
      }
    }
    return $schemas;
  }
  
  private function findModels() {
    if (!is_dir($this->p('app/Models')))
      return array();
    $dir = new \DirectoryIterator($this->p('app/Models'));
    $models = array();
    foreach ($dir as $file) {
      if (!$file->isDot()) {
        if (preg_match('/^(.+)\.php$/', $file->getFilename(), $matches) === 1)
          $models[] = $matches[1];
      }
    }
    return $models;
  }
  
  private function findControllers(\RecursiveDirectoryIterator $dir = null, $prefix = '') {
    if (!isset($dir)) {
      if (!is_dir($this->p('app/Controllers')))
        return array();
      $dir = new \RecursiveDirectoryIterator($this->p('app/Controllers'));
    }
    $controllers = array();
    foreach ($dir as $file) {
      if ($dir->hasChildren()) {
        $controllers = array_merge(
          $controllers,
          $this->findControllers($dir->getChildren(), $file->getFilename() . '\\')
        );
      }
      else if($file->isFile()){
        if (preg_match('/^(.+)Controller\.php$/', $file->getFilename(), $matches) === 1)
          $controllers[] = $prefix . $matches[1];
      }
    }
    return $controllers;
  }
  
  private function findSnippets(\RecursiveDirectoryIterator $dir = null, $prefix = '') {
    if (!isset($dir)) {
      if (!is_dir($this->p('app/Snippets')))
        return array();
      $dir = new \RecursiveDirectoryIterator($this->p('app/Snippets'));
    }
    $snippets = array();
    foreach ($dir as $file) {
      if ($dir->hasChildren()) {
        $snippets = array_merge(
          $snippets,
          $this->findSnippets($dir->getChildren(), $file->getFilename() . '\\')
        );
      }
      else if($file->isFile()){
        if (preg_match('/^(.+)\.php$/', $file->getFilename(), $matches) === 1)
          $snippets[] = $prefix . $matches[1];
      }
    }
    return $snippets;
  }
}