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
use Jivoo\AccessControl\AuthHelper;
use Jivoo\AccessControl\SingleUserModel;

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
  
  private $authHelper = null;
  
  private $lock = null;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->lock = new Config($this->p('user', 'lock.php'));
    if ($this->lock->get('enable', false)) {
      $auth = $this->getAuth();
      if (!$auth->isLoggedIn()) {
        $this->view->addTemplateDir($this->p('templates'));
        if ($this->request->path === array('setup')) {
          $login = $this->m->Snippets->getSnippet('Jivoo\Setup\Login');
          $login->enableLayout();
          $this->m->Routing->customDispatch($login, array($auth));
        }
        $this->m->Routing->customDispatch(
          array($this->view, 'render'),
          'setup/maintenance.html'
        );
      }
    }
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
  
  public function getAuth() {
    if (!isset($this->authHelper)) {
      $this->authHelper = new AuthHelper($this->app);
      $this->authHelper->sessionPrefix = 'setup_auth_';
      $this->authHelper->cookiePrefix = 'setup_auth_';
      $this->authHelper->userModel = new SingleUserModel(
        $this->session, 'test', $this->authHelper->passwordHasher->hash('test')
      );
      $this->authHelper->authentication = 'Form';
    }
    return $this->authHelper;
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
