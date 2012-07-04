<?php

abstract class ApplicationHelper {

  
  protected $m = NULL;
  protected $request = NULL;

  protected $controller = NULL;

  public final function __construct(Templates $templates, Routes $routes, $controller = NULL) {
    $this->m = new Dictionary();
    $this->m->Templates = $templates;
    $this->m->Routes = $routes;
    
    $this->request = $routes->getRequest();

    $this->controller = $controller;

    $this->init();
  }

  protected function init() {
  }
  
  protected function getLink($route) {
    return $this->m->Routes->getLink($route);
  }

}
