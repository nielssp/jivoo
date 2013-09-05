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
   * @var UserSession Current user session if logged in
   */
  private $userSession = null;

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
    if (isset($this->session['auth_session'])) {
      $ip = $this->request->ip;
      $session = $this->m->Models->UserSession->find($this->session['auth_session']);
      if ($session) {
        if ($session->hasExpired()) {
          $session->delete();
        }
        else {
          $this->userSession = $session;
          $this->user = $session->getUser();
          return true;
        }
      }
      unset($this->session['auth_session']);
    }
    return false;
  }

  /**
   * Check cookie for logged in user
   * @return boolean True if logged in, false otherwise
   */
  protected function checkCookie() {
    if (isset($this->request->cookies['auth_session'])) {
      $session = $this->m->Models->UserSession->find($this->request->cookies['auth_session']);
      if ($session) {
        if ($session->hasExpired()) {
          $session->delete();
        }
        else {
          $this->userSession = $session;
          $this->user = $session->getUser();
          return true;
        }
      }
      unset($this->request->cookies['auth_session']);
    }
    return false;
  }

  /**
   * Create session to log user in
   * @param bool $remember Whether or not to remember log in (set cookie)
   * @throws Exception if unable to save user session data
   */
  protected function createSession($remember = false) {
    $ip = $this->request->ip;
    $session = $this->m->Models->UserSession->create();
    $session->id = $this->m->Shadow->genUid();
    $session->setUser($this->user);
    if ($remember) {
      $session->valid_until = time() + 60 * 60 * 24 * 14; // 14 days
      $this->request->cookies['auth_session'] = $session->id;
    }
    else {
      $session->valid_until = time() + 60 * 30; // 30 minutes
      $this->session['auth_session'] = $session->id;
      // TODO: Auto renew this type of session
    }
    if (!$session->save()) {
      throw new Exception(tr('Could not create session.'));
    }
    $this->userSession = $session;
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
    $this->createSession($remember);
    return true;
  }

  /**
   * Log out of current user and unset sessions and cookies
   */
  public function logOut() {
    if (isset($this->userSession)) {
      $this->userSession->delete();
    }
    if (isset($this->session['auth_session'])) {
      unset($this->session['auth_session']);
    }
    if (isset($this->request->cookies['auth_session'])) {
      unset($this->request->cookies['auth_session']);
    }
    $this->userSession = null;
    $this->user = null;
  }
  
  /**
   * Garbage collect expired sessions
   */
  public function garbageCollect() {
    return $this->m->Database->usersessions->delete()
      ->where('valid_until <= ?', time())
      ->execute();
  }
}
