<?php
// Module
// Name           : Authentication
// Description    : The Apakoh Core authentication system
// Author         : apakoh.dk
// Dependencies   : Core/Shadow
//                  Core/Setup Core/Templates Core/Database
//                  Core/Routing Core/Helpers Core/Models

/**
 * Authentication module
 *
 * @package Core\Authentication
 */
class Authentication extends ModuleBase {
  /**
   * @var User Current user if logged in
   */
  private $user = null;

  /**
   * @var Group The group associated with unregisterd visitors (guests)
   */
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
      $this->view->addTemplateDir($this->p('templates'), 3);
      $controller->rootGroup = $rootGroup;
      $this->m->Setup->enterSetup($controller, 'setupRoot');
    }
    
    $authHelper = new AuthHelper($this->m->Routing);
    $authHelper->addModule($this);
    $authHelper->addModule($this->m->Shadow);
    $this->m->Helpers->addHelper($authHelper);

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

  /**
   * Check if anyone is logged in
   * @return boolean True if logged in, false otherwise
   */
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

  /**
   * Check current user or guest for a permission
   * @param string $permission Permission key
   * @return boolean True if user has permission, false otherwise
   */
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
  
  /**
   * Get default group for registered users
   * @return Group|false A group or false if unavailable
   */
  public function getDefaultGroup() {
    return $this->m->Models->Group->first(
      SelectQuery::create()
        ->where('name = ?', $this->config['defaultGroups']['registered'])
    );
  }

  /**
   * Get the current user
   * @return User|false The current user or false if not logged in
   */
  public function getUser() {
    return $this->isLoggedIn() ? $this->user : false;
  }
  
  /**
   * Check session for logged in user
   * @return boolean True if logged in, false otherwise
   */
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

  /**
   * Check cookie for logged in user
   * @return boolean True if logged in, false otherwise
   */
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

  /**
   * Create session to log user in
   * @param bool $remember Whether or not to remember log in (set cookie)
   * @throws Exception if unable to save user session data
   */
  protected function setSession($remember = false) {
    /** @TODO rethink sessions */
    $this->session->regenerate();
    $sid = $this->session->id;
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

  /**
   * Log in to user
   * @param string $username Username
   * @param string $password Password
   * @param bool $remember Whether or not to remember log in (set cookie)
   * @return bool True if successful, false otherwise
   */
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

  /**
   * Log out of current user and unset sessions and cookies
   */
  public function logOut() {
    $this->sessionDefaults();
    if (isset($this->request->cookies['login'])) {
      unset($this->request->cookies['login']);
    }
    $this->user = null;
  }

  /**
   * Unset sessions
   */
  protected function sessionDefaults() {
    unset($this->session['username']);
  }
}
