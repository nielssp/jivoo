<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\LoadModuleEvent;
use Jivoo\Routing\TextResponse;
use Jivoo\Core\Lib;

/**
 * Installation, setup, maintenance, update and recovery system..
 */
class Setup extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers', 'Routing', 'Snippets', 'View');
  
  /**
   * @var InstallerSnippet[] Installers.
   */
  private $installers = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
  }

  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if (isset($this->app->appConfig['install'])) {
      $installer = $this->app->appConfig['install'];
      if (!Lib::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      if (!$this->config[$installer]->get('done', false)) {
        $snippet = $this->getInstaller($this->app->appConfig['install']);
        $this->view->addTemplateDir($this->p('templates'));
        $response = $snippet();
        if (is_string($response))
          $response = new TextResponse($snippet->getStatus(), 'text/html', $response);
        $this->m->Routing->respond($response);
      }
    }
  }
  
  public function getInstaller($class) {
    if (!isset($this->installers[$class])) {
      $snippet = $this->m->Snippets->getSnippet($class);
      assume($snippet instanceof InstallerSnippet);
      $this->installers[$class] = $snippet;
    }
    return $this->installers[$class];
  }
}
