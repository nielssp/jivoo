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
      'showExceptions' => false,
    );
    ErrorReporting::setHandler(array($this, 'handleException'));
  }

  public function handleException(Exception $exception) {
    if ($this->config['showExceptions']) {
      $app = $this->app->name;
      $version = $this->app->version;
      $title = tr('Uncaught exception');
      include $this->p('layout.php');
      exit;
    }
    else {
      include $this->p('error.php');
      exit;
    }
    /** @todo attempt to create error report */
  }

}
