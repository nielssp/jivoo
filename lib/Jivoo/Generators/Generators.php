<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Generators;

use Jivoo\Core\LoadableModule;

/**
 * Generators module for generating Jivoo applications.
 * @package Jivoo\Generators
 */
class Generators extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Controllers', 'Routing', 'View');

  /**
   * {@inheritdoc}
   */
  protected function init() {
  }
  
  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if (empty($this->app->appConfig)) {
      $controller = $this->m->Controllers->getController('Generator');
      $controller->autoRoute('index');
      $controller->autoRoute('configure');
      $this->m->Routing->setRoot('Generator::index');
      $this->view->addTemplateDir($this->p('templates'));
    }
  }
}
