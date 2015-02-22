<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Generators;

use Jivoo\Core\LoadableModule;

/**
 * Generators module for generating Jivoo applications.
 */
class Generators extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Snippets', 'Routing', 'View', 'Assets');

  /**
   * {@inheritdoc}
   */
  protected function init() {
  }
  
  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if ($this->app->noAppConfig) {
      $this->m->Routing->routes->root('snippet:Jivoo\Generators\Index');
      $this->m->Routing->routes->auto('snippet:Jivoo\Generators\Index');
      $this->m->Routing->routes->auto('snippet:Jivoo\Generators\Configure');
      $this->view->addTemplateDir($this->p('templates'));
    }
  }
}
