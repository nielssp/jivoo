<?php

abstract class ApplicationTemplate {

  private $m = NULL;

  private $controller = NULL;

  private $templatePaths = array();

  protected $data = array();

  public final function __construct(Templates $templates, Routes $routes, $controller = NULL) {
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routes = $routes;

    $this->controller = $controller;
  }

  public function __get($name) {
    return $this->get($name);
  }
  
  public function __set($name, $value) {
    $this->set($name, $value);
  }

  public function get($name) {
    if (isset($this->data[$name])) {
      return $this->data[$name];
    }
    return NULL;
  }

  public function set($name, $value = NULL) {
    if (is_array($name)) {
      foreach ($name as $n => $value) {
        $this->set($n, $value);
      }
    }
    else {
      $this->data[$name] = $value;
    }
  }

  protected function link($route = NULL) {
    return $this->m->Routes->getLink($route);
  }

  protected function file($file) {
    return $this->m->Templates->getFile($file);
  }

  protected function insertScript($id, $file, $dependencies = array()) {
    $this->m->Templates->insertScript($id, $file, $dependencies);
  }

  protected function insertStyle($id, $file, $dependencies = array()) {
    $this->m->Templates->insertStyle($id, $file, $dependencies);
  }

  protected function insertMeta($id, $file, $dependencies = array()) {
    $this->m->Templates->insertMeta($id, $file, $dependencies);
  }

  protected function setIndent($indentation = 0) {
    $this->m->Templates->setHtmlIndent($indentation);
  }

  protected function output($location, $linePrefix = '') {
    $this->m->Templates->outputHtml($location, $linePrefix);
  }

  public function setTemplatePaths($paths) {
    $this->templatePaths = $paths;
  }

  protected function getTemplate($template) {
    return $this->m->Templates->getTemplate($template, $this->templatePaths);
  }

  protected function getTemplateData($template) {
    return $this->m->Templates->getTemplateData($template);
  }

  public abstract function render($template);

}
