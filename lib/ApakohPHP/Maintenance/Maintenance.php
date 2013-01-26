<?php
// Module
// Name           : Maintenance
// Version        : 0.3.0
// Description    : The PeanutCMS maintenance system
// Author         : PeanutCMS
// Dependencies   : ApakohPHP/Shadow ApakohPHP/Errors ApakohPHP/Routes

/**
 * Maintenance module
 * @package PeanutCMS
 * @subpackage Modules
 */
class Maintenance extends ModuleBase {
  protected function init() {}

  /**
   * Present a setup page then exit
   * @param ApplicationController $controller Controller for page
   * @param string $action Action to call in controller
   * @param mixed[] $parameters Additional parameters for action
   */
  public function setup(ApplicationController $controller, $action = 'index',
                        $parameters = array()) {
    $this->m
      ->Routes
      ->setRoute($controller, $action, 10, $parameters);
    $this->m
      ->Routes
      ->callController();
    exit;
  }
}
