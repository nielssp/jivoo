<?php

abstract class ApplicationTemplate {

  private $m = null;

  private $controller = null;

  private $templatePaths = array();

  private $request;

  protected $data = array();

  public final function __construct(Templates $templates, Routing $routing,
                                    $controller = null) {
    $this->m = new Dictionary();
    $this->m
      ->Templates = $templates;
    $this->m
      ->Routing = $routing;

    $this->request = $this->m
      ->Routing
      ->getRequest();

    $this->controller = $controller;
    
    $this->data['messages'] = $this->request->session->messages;
    $this->data['alerts'] = $this->request->session->alerts;
    $this->data['notices'] = $this->request->session->notices;
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
    return null;
  }

  public function set($name, $value = null) {
    if (is_array($name)) {
      foreach ($name as $n => $value) {
        $this->set($n, $value);
      }
    }
    else {
      $this->data[$name] = $value;
    }
  }

  protected function link($route = null) {
    return $this->m
      ->Routing
      ->getLink($route);
  }

  protected function isCurrent($route = null) {
    return $this->m
      ->Routing
      ->isCurrent($route);
  }

  protected function file($file) {
    return $this->m
      ->Templates
      ->getFile($file);
  }

  protected function insertScript($id, $file, $dependencies = array()) {
    $this->m
      ->Templates
      ->insertScript($id, $file, $dependencies);
  }

  protected function requestScript($id) {
    return $this->m
      ->Templates
      ->requestHtml($id);
  }

  protected function requestStyle($id) {
    return $this->m
      ->Templates
      ->requestHtml($id);
  }

  protected function insertStyle($id, $file, $dependencies = array()) {
    $this->m
      ->Templates
      ->insertStyle($id, $file, $dependencies);
  }

  protected function insertMeta($id, $file, $dependencies = array()) {
    $this->m
      ->Templates
      ->insertMeta($id, $file, $dependencies);
  }

  protected function setIndent($indentation = 0) {
    $this->m
      ->Templates
      ->setHtmlIndent($indentation);
  }

  protected function output($location, $linePrefix = '') {
    $this->m
      ->Templates
      ->outputHtml($location, $linePrefix);
  }

  public function setTemplatePaths($paths) {
    $this->templatePaths = $paths;
  }

  protected function getTemplate($template, $return = false) {
    return $this->m
      ->Templates
      ->getTemplate($template, $this->templatePaths, $return);
  }

  protected function getTemplateData($template) {
    return $this->m
      ->Templates
      ->getTemplateData($template);
  }

  public abstract function render($template, $return = false);

}
