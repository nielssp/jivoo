<?php
// Module
// Name           : Maintenance
// Version        : 0.3.0
// Description    : The ApakohPHP maintenance system
// Author         : apakoh.dk
// Dependencies   : ApakohPHP/Shadow ApakohPHP/Routing

/**
 * Maintenance module
 * @package PeanutCMS
 * @subpackage Modules
 */
class Maintenance extends ModuleBase {
  protected function init() {
    $this->config->defaults = array(
    );
  }

  /**
   * Present a setup page then exit
   * @param ApplicationController $controller Controller for page
   * @param string $action Action to call in controller
   * @param mixed[] $parameters Additional parameters for action
   */
  public function setup(ApplicationController $controller, $action = 'index',
                        $parameters = array()) {
    $this->m->Routing->setRoute($controller, $action, 10, $parameters);
    $this->m->Routing->callController();
    exit;
  }
}
