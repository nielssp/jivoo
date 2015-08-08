<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\LoadModuleEvent;
use Jivoo\Routing\TextResponse;
use Jivoo\Routing\InvalidResponseException;
use Jivoo\Routing\Response;
use Jivoo\Core\Store\Config;
use Jivoo\Core\Store\PhpStore;
use Jivoo\AccessControl\AuthHelper;
use Jivoo\AccessControl\SingleUserModel;
use Jivoo\Core\Store\Document;
use Jivoo\Core\Event;
use Jivoo\Core\Utilities;
use Jivoo\Core\Store\StateInvalidException;

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
    $lockFile = new PhpStore($this->p('user', 'lock.php'));
    $this->lock = new Config($lockFile);
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
    $state = $this->state->read('setup');
    if (isset($this->app->manifest['install'])) {
      $installer = $this->app->manifest['install'];
      if (!Utilities::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      if (!$state[$installer]->get('done', false)) {
        $state = $this->state->write('setup');
        $this->dispatchInstaller($installer, $state[$installer]);
      }
    }
    if (isset($this->app->manifest['update'])) {
      if (!isset($state['version'])) {
        $state = $this->state->write('setup');
        $state['version'] = $this->app->version;
      }
      else if ($state['version'] !== $this->app->version) {
        $state = $this->state->write('setup');
        $installer = $this->app->manifest['update'];
        if (!Utilities::classExists($installer))
          $installer = $this->app->n('Snippets\\' . $installer);
        $installerState = $state['updates'][$this->app->version][$installer];
        if ($installerState->get('done', false)) {
          $state['version'] = $this->app->version;
        }
        else {
          $this->dispatchInstaller($installer, $installerState);
        }
      }
    }
    if (isset($state['current']['install'])) {
      $state = $this->state->write('setup');
      $installer = $state['current']->get('install', null);
      if (!Utilities::classExists($installer))
        $installer = $this->app->n('Snippets\\' . $installer);
      $installerState = $state['current'][$installer];
      if ($installerState->get('done', false)) {
        unset($state['current']);
      }
      else {
        $this->dispatchInstaller($installer, $installerState);
      }
    }
    $state->close();
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
   * Dispatch an installer.
   * @param string $installer Name of installer class.
   * @param Document $installerState Installer state document if any.
   * @throws InvalidResponseException If the installer returns an invalid
   * response.
   */
  public function dispatchInstaller($installer, Document $installerState = null) {
    $snippet = $this->getInstaller($installer, $installerState);
    $this->app->attachEventHandler('beforeStop', function(Event $event) {
      $event->sender->state->close('setup');
    });
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
  
  /**
   * Start an installer manually.
   * @param string $installerClass Installer class.
   * @throws \Exception If the installer could not be started.
   */
  public function trigger($installerClass) {
    if (!Utilities::classExists($installerClass))
      $installerClass = $this->app->n('Snippets\\' . $installerClass);
    $this->logger->info(tr('Trigger installer: %1', $installerClass));
    $this->getInstaller($installerClass);
    $state = $this->state->write('setup');
    unset($state['current']);
    $state['current']['install'] = $installerClass;
    $state->close();
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
    $this->logger->info(tr('Enable lock'));
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
    unset($this->lock['session']);
    if ($deleteCredentials) {
      unset($this->lock['username']);
      unset($this->lock['password']);
    }
    $this->logger->info(tr('Disable lock'));
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
   * @param Document $state Installer state.
   * @return InstallerSnippet Instalelr.
   */
  public function getInstaller($class, Document $state = null) {
    if (!isset($this->installers[$class])) {
      if (!isset($state) and $this->state->isMutable('setup')) {
        $state = $this->state->write('setup');
        $state = $state[$class];
      }
      $snippet = $this->m->Snippets->getSnippet($class);
      assume($snippet instanceof InstallerSnippet);
      if (isset($state))
        $snippet->setState($state);
      $this->installers[$class] = $snippet;
    }
    return $this->installers[$class];
  }
}
