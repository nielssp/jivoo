<?php
// Module
// Name           : Maintenance
// Version        : 0.3.0
// Description    : The PeanutCMS maintenance system
// Author         : PeanutCMS
// Dependencies   : Shadow Errors Routes

class Maintenance extends ModuleBase {
  protected function init() {
  }

  public function setup(ApplicationController $controller, $action = 'index', $parameters = array()) {
    $this->m->Routes->setRoute($controller, $action, 10, $parameters);
    $this->m->Routes->callController();
    exit;
  }
}
