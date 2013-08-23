<?php
// Module
// Name           : Setup
// Description    : The Apakoh Core installation/setup system.
// Author         : apakoh.dk
// Dependencies   : Core/Controllers Core/Routing Core/Templates Core/Assets

/**
 * Setup module.
 * @package Core\Setup
 */
class Setup extends ModuleBase {
  protected function init() {
  }

  /**
   * Enter a setup controller and execute an action. Will add setup templates
   * to template path, and set the variable 'basicStyle' to point at a basic
   * stylesheet.
   * @param Controller $controller Setup controller
   * @param string $action Action to execute
   */
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
