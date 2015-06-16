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
  
  /**
   * @var AuthHelper Authentication for maitnenance user.
   */
  private $authHelper = null;
  
  /**
   * @var Config Lock.
   */
  private $lock = null;
  
  /**
   * @var bool Whether or not an install is in progress. 
   */
  private $active = false;

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->lock = new Config($this->p('user', 'lock.php'));
    if ($this->lock->get('enable', false)) {
      $auth = $this->getAuth();
      if (!$auth->isLoggedIn()) {
        if ($this->request->path === array('setup')) {
          $login = $this->m->Snippets->getSnippet('Jivoo\Setup\Login');
          $login->enableLayout();
          $response = $this->m->Routing->dispatch($login, array($auth));
        }
        else {
          $response = $this->m->Routing->dispatch(
            array($this->view, 'render'),
            'jivoo/setup/maintenance.html'
          );
        }
        $this->m->Routing->respond($response);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function afterLoad() {
    if (isset($this->app->manifest['install'])) {
      $installer = $this->app->manifest['install'];
      if (!Lib::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      if (!$this->config[$installer]->get('done', false)) {
        $snippet = $this->getInstaller($installer);
        try {
          $this->active = true;
          $this->m->Routing->respond(
            $this->m->Routing->dispatch($snippet)
          );
        }
        catch (InvalidResponseException $e) {
          throw new InvalidResponseException(tr(
            'An invalid response was returned from installer step: %1',
            $snippet->getCurrentStep()
          ), null, $e);
        }
      }
    }
    if (isset($this->app->manifest['update'])) {
      if ($this->config->get('version', $this->app->version) !== $this->app->version) {
        $installer = $this->app->manifest['update'];
        if (!Lib::classExists($installer))
          $installer = $this->app->n('Snippets\\' . $installer);
        $config = $this->config['updates'][$this->app->version][$installer];
        if ($config->get('done', false)) {
          $this->config['version'] = $this->app->version;
        }
        else {
          $snippet = $this->getInstaller($installer, $config);
          try {
            $this->active = true;
            $this->m->Routing->respond(
              $this->m->Routing->dispatch($snippet)
            );
          }
          catch (InvalidResponseException $e) {
            throw new InvalidResponseException(tr(
              'An invalid response was returned from updater step: %1',
              $snippet->getCurrentStep()
            ), null, $e);
          }
        }
      }
    }
    if (isset($this->config['current']['install'])) {
      $installer = $this->config['current']->get('install', null);
      if (!Lib::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      $config = $this->config['current'][$installer];
      if ($config->get('done', false)) {
        unset($this->config['current']);
      }
      else {
        $snippet = $this->getInstaller($installer, $config);
        try {
          $this->active = true;
          $this->m->Routing->respond(
            $this->m->Routing->dispatch($snippet)
          );
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
  
  /**
   * Get lock config.
   * @return Config Lock.
   */
  public function getLock() {
    return $this->lock;
  }
  
  /**
   * Whether or not the lock is enabled.
   * @return bool True if enabled.
   */
  public function isLocked() {
    return $this->lock->get('enable', false);
  }
  
  /**
   * Whether or not an install is in progress.
   * @return bool True if install in progress.
   */
  public function isActive() {
    return $this->active;
  }
  
  /**
   * Start an installer manually.
   * @param string $installerClass Installer class.
   * @throws \Exception If the installer could not be started.
   */
  public function trigger($installerClass) {
    if (!Lib::classExists($installerClass))
      $installerClass = $this->app->n('Snippets\\' . $installerClass);
    $this->getInstaller($installerClass);
    unset($this->config['current']);
    $this->config['current']['install'] = $installerClass;
    if (!$this->config->save())
      throw new \Exception(tr('Could not start installer, config could not be saved.'));
    $this->m->Routing->refresh();
  }
  
  /**
   * Enter maintenance mode.
   * @param string $username Maitnenance username.
   * @param string $passwordHash Maitnenance password hash.
   * @param bool $authenticated Whether or not to authenticate user autoamtically.
   * @return bool True if maintenance mode enabled.
   */
  public function lock($username = null, $passwordHash = null, $authenticated = true) {
    $this->lock['enable'] = true;
    if (isset($username) and isset($passwordHash)) {
      $this->lock['username'] = $username;
      $this->lock['password'] = $passwordHash;
      if ($authenticated) {
        $auth = $this->getAuth();
        $auth->createSession(array('user' => $username));
      }
    }
    return $this->lock->save();
  }
  
  /**
   * Exit maintenance mode.
   * @param bool $deleteCredentials Whether or not to delete the saved username
   * and password.
   * @return bool True if maintenance mode disabled.
   */
  public function unlock($deleteCredentials = true) {
    $this->lock['enable'] = false;
    if ($deleteCredentials) {
      unset($this->lock['username']);
      unset($this->lock['password']);
    }
    return $this->lock->save();
  }
  
  /**
   * Get authentication helper for maintenance user. 
   * @return AuthHelper Authentication helper.
   */
  public function getAuth() {
    if (!isset($this->authHelper)) {
      $this->authHelper = new AuthHelper($this->app);
      $this->authHelper->sessionPrefix = 'setup_auth_';
      $this->authHelper->cookiePrefix = 'setup_auth_';
      $this->authHelper->userModel = new MaintenanceUserModel($this->lock);
      $this->authHelper->authentication = 'Form';
    }
    return $this->authHelper;
  }
  
  /**
   * Get an installer.
   * @param string $class Installer class.
   * @param string $config Installer state.
   * @return InstallerSnippet Instalelr.
   */
  public function getInstaller($class, $config = null) {
    if (!isset($this->installers[$class])) {
      if (!isset($config))
        $config = $this->config[$class];
      $snippet = $this->m->Snippets->getSnippet($class);
      assume($snippet instanceof InstallerSnippet);
      $snippet->setConfig($config);
      $this->installers[$class] = $snippet;
    }
    return $this->installers[$class];
  }
}
