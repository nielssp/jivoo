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
  }

}
