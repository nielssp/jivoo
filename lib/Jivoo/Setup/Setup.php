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
use Jivoo\Routing\InvalidResponseException;
use Jivoo\Routing\Response;
use Jivoo\Core\Config;

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
    $lockFile = $this->p('user', 'lock.php');
    if (file_exists($lockFile)) {
      $lock = new Config($lockFile);
      if ($lock->get('enable', false)) {
        $this->view->addTemplateDir($this->p('templates'));
        $this->m->Routing->customDispatch(
          array($this->view, 'render'),
          'setup/maintenance.html'
        );
      }
    }
    if (isset($this->app->appConfig['install'])) {
      $installer = $this->app->appConfig['install'];
      if (!Lib::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      if (!$this->config[$installer]->get('done', false)) {
        $snippet = $this->getInstaller($this->app->appConfig['install']);
        $this->view->addTemplateDir($this->p('templates'));
        try {
          $this->m->Routing->customDispatch($snippet);
        }
        catch (InvalidResponseException $e) {
          throw new InvalidResponseException(tr(
            'An invalid response was returned from installer step: %1',
            $snippet->getCurrentStep()
          ), null, $e);
        }
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
