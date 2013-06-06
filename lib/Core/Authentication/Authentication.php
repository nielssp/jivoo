<?php
// Module
// Name           : Authentication
// Version        : 0.2.0
// Description    : The Apakoh Core authentication system
// Author         : apakoh.dk
// Dependencies   : Core/Shadow
//                  Core/Setup Core/Templates Core/Database
//                  Core/Routing Core/Helpers Core/Models

/**
 * Authentication module
 *
 * @package Core
 * @subpackage Authentication
 */
class Authentication extends ModuleBase {

  private $user = null;

  private $unregistered = null;

  protected function init() {
    $this->config->defaults = array(
      'defaultGroups' => array(
        'unregistered' => 'guests',
        'registered' => 'users',
      ),
      'rootCreated' => false, 
    );

    $rootGroup = null;
    if ($this->m->Database->isNew('groups')) {
      $group = $this->m->Models->Group->create();
      $group->name = 'root';
      $group->title = tr('Admin');
      $group->save();
      $group->setPermission('*', true);
      $rootGroup = $group;

      $group = $this->m->Models->Group->create();
      $group->name = 'users';
      $group->title = tr('User');
      $group->save();
      $group->setPermission('frontend', true);

      $group = $this->m->Models->Group->create();
      $group->name = 'guests';
      $group->title = tr('Guest');
      $group->save();
      $group->setPermission('frontend', true);

    }

    if ($this->m->Database->isNew('users') OR $this->config['rootCreated'] !== true) {
      $this->config['rootCreated'] = false;
      Logger::debug('Authentication: No root user created');
      $controller = new AuthenticationSetupController(
        $this->m->Routing, $this->m->Templates, $this->config
      );
      $controller->addModule($this->m->Shadow);
      $this->m->Helpers->addHelpers($controller);
      $this->m->Models->addModels($controller);
      $controller->addTemplatePath($this->p('templates'));
      $controller->rootGroup = $rootGroup;
      $this->m->Setup->enterSetup($controller, 'setupRoot');
    }

    if (!$this->isLoggedIn()) {
      $unregistered = $this->m->Models->Group->first(
        SelectQuery::create()
          ->where('name = ?', $this->config['defaultGroups']['unregistered'])
      );
      if ($unregistered) {
        $this->unregistered = $unregistered;
      }
    }
  }

  public function isLoggedIn() {
    if (isset($this->user)) {
      return true;
    }
    if ($this->checkSession()) {
      return true;
    }
    if ($this->checkCookie()) {
      return true;
    }
    return false;
  }

  public function hasPermission($permission) {
    if ($this->isLoggedIn()) {
      return $this->user->hasPermission($permission);
    }
    else if (isset($this->unregistered)) {
      return $this->unregistered->hasPermission($permission);
    }
    else {
      return false;
    }
  }

  public function getUser() {
    return $this->isLoggedIn() ? $this->user : false;
  }

  protected function checkSession() {
    if (isset($this->session['username'])) {
      $sid = session_id();
      $ip = $_SERVER['REMOTE_ADDR'];
      $user = $this->m->Models->User->first(
        SelectQuery::create()
          ->where('username = ?', $this->session['username'])
          ->and('session = ?', $sid)->and('ip = ?', $ip)
      );
      if ($user) {
        $this->user = $user;
        return true;
      }
    }
    return false;
  }

  protected function checkCookie() {
    if (isset($this->request->cookies['login'])) {
      list($username, $cookie) = explode(':', $this->request->cookies['login']);
      $user = $this->m->Models->User->first(
        SelectQuery::create()->where('username = ?', $username)
          ->and('cookie = ?', $cookie)
      );
      if ($user) {
        $this->user = $user;
        return true;
      }
      else {
        unset($this->request->cookies['login']);
      }
    }
    return false;
  }

  protected function setSession($remember = false) {
    session_regenerate_id();
    $sid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = $this->user->username;
    $cookie = $this->user->cookie;
    $this->session['username'] = $username;
    if ($remember) {
      $cookie = md5($username . rand() . time());
      $cookieval = implode(':', array($username, $cookie));
      $this->request->cookies['login'] = $cookieval;
    }
    $this->user->session = $sid;
    $this->user->cookie = $cookie;
    $this->user->ip = $ip;
    if (!$this->user->save(array('validate' => false))) {
      throw new Exception(tr('Could not save user session data.'));
    }

  }

  public function logIn($username, $password, $remember = false) {
    $user = $this->m->Models->User
      ->first(SelectQuery::create()->where('username = ?', $username));
    if (!$user) {
      return false;
    }
    if (!$this->m->Shadow->compare($password, $user->password)) {
      return false;
    }
    $this->user = $user;
    $this->setSession($remember);
    return true;
  }

  public function logOut() {
    $this->sessionDefaults();
    if (isset($this->cookies['login'])) {
      unset($this->request->cookies['login']);
    }
    $this->user = null;
  }

  protected function sessionDefaults() {
    unset($this->session['username']);
  }
}
