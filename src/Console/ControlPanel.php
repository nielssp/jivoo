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
    $dir = new \DirectoryIterator($this->p('app/Schemas'));
    $schemas = array();
    foreach ($dir as $file) {
      if (!$file->isDot()) {
        $class = $this->app->n('Schemas\\' . preg_replace('/\.php$/', '', $file->getFilename()));
        $object = new $class();
        $schemas[] = $object;
      }
    }
    return $schemas;
  }
  
  private function findModels() {
    $dir = new \DirectoryIterator($this->p('app/Models'));
    $models = array();
    foreach ($dir as $file) {
      if (!$file->isDot()) {
        $model = preg_replace('/\.php$/', '', $file->getFilename());
        $models[] = $model;
      }
    }
    return $models;
  }
  
  private function findControllers(\RecursiveDirectoryIterator $dir = null, $prefix = '') {
    if (!isset($dir))
      $dir = new \RecursiveDirectoryIterator($this->p('app/Controllers'));
    $controllers = array();
    foreach ($dir as $file) {
      if ($dir->hasChildren()) {
        $controllers = array_merge(
          $controllers,
          $this->findControllers($dir->getChildren(), $file->getFilename() . '\\')
        );
      }
      else if($file->isFile()){
        $controller = preg_replace('/Controller\.php$/', '', $file->getFilename());
        $controllers[] = $prefix . $controller;
      }
    }
    return $controllers;
  }
  
  private function findSnippets(\RecursiveDirectoryIterator $dir = null, $prefix = '') {
    if (!isset($dir))
      $dir = new \RecursiveDirectoryIterator($this->p('app/Snippets'));
    $snippets = array();
    foreach ($dir as $file) {
      if ($dir->hasChildren()) {
        $snippets = array_merge(
          $snippets,
          $this->findControllers($dir->getChildren(), $file->getFilename() . '\\')
        );
      }
      else if($file->isFile()){
        $snippet = preg_replace('/\.php$/', '', $file->getFilename());
        $snippets[] = $prefix . $snippet;
      }
    }
    return $snippets;
  }
}