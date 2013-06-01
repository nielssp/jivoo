<?php
// Module
// Name           : Maintenance
// Version        : 0.3.0
// Description    : The Apakoh Core maintenance system
// Author         : apakoh.dk
// Dependencies   : Core/Shadow Core/Routing

/**
 * Maintenance module
 * @package Core
 * @subpackage Modules
 */
class Maintenance extends ModuleBase {
  protected function init() {
    $this->config->defaults = array(
    );
  }

  /**
   * Present a setup page then exit
   * @param Controller $controller Controller for page
   * @param string $action Action to call in controller
   * @param mixed[] $parameters Additional parameters for action
   */
  public function setup(Controller $controller, $action = 'index',
                        $parameters = array()) {
    $this->m->Routing->setRoute($controller, $action, 10, $parameters);
    $this->m->Routing->callController();
    exit;
  }
}
