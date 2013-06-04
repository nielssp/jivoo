<?php
// Module
// Name           : Setup
// Version        : 0.3.0
// Description    : The Apakoh Core installation/setup system.
// Author         : apakoh.dk
// Dependencies   : Core/Controllers Core/Routing Core/Templates Core/Assets

/**
 * Setup module.
 * @package Core
 * @subpackage Setup
 */
class Setup extends ModuleBase {
  protected function init() {
  }

  
  public function enterSetup(Controller $controller, $action = 'index') {
    $controller->addModule($this);
    $controller->addTemplatePath($this->p('templates'));
    $controller->basicStyle = $this->m->Assets->getAsset('core', 'ui/basic.css');
    $this->m->Controllers->addController($controller);
    $controller->autoRoute($action);
    $this->m->Routing->reroute($controller, $action);
    $this->m->Routing->findRoute();
    $this->app->stop();
  }

}
