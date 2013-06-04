<?php
// Module
// Name           : Setup
// Version        : 0.3.0
// Description    : The Apakoh Core installation/setup system.
// Author         : apakoh.dk

/**
 * Setup module
 * @package Core
 * @subpackage Setup
 */
class Setup extends ModuleBase {
  protected function init() {
    $this->config->defaults = array(
    );
    ErrorReporting::setHandler(array($this, 'handleException'));
  }

  public function handleException(Exception $exception) {
    // if something
    $app = $this->app->name;
    $version = $this->app->version;
    $title = 'Uncaught exception';
    include $this->p('layout.php');
    exit;
  }

}
