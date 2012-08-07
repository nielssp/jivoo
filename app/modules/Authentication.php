<?php
// Module
// Name           : Authentication
// Version        : 0.2.0
// Description    : The PeanutCMS authentication system
// Author         : PeanutCMS
// Dependencies   : Errors Configuration Shadow Templates Actions Database Routes Http

/**
 * Authentication module
 *
 * @package PeanutCMS
 */

/**
 * Authentication class
 */
class Authentication extends ModuleBase {

  private $user = NULL;
  
  private $unregistered = NULL;

  protected function init() {
    $newInstall = FALSE;

    $usersSchema = new usersSchema();
    $groupsSchema = new groupsSchema();
    $groups_permissionsSchema = new groups_permissionsSchema();

    $this->m->Database->migrate($usersSchema);
    $newInstall = $this->m->Database->migrate($groupsSchema) == 'new';
    $this->m->Database->migrate($groups_permissionsSchema);

    $this->m->Database->users->setSchema($usersSchema);
    $this->m->Database->groups->setSchema($groupsSchema);
    $this->m->Database->groups_permissions->setSchema($groups_permissionsSchema);

    User::connect($this->m->Database->users);
    Group::connect($this->m->Database->groups);

    if ($newInstall) {
      $group = Group::create();
      $group->name = 'root';
      $group->title = tr('Admin');
      $group->save();
      $group->setPermission('*', TRUE);

      $group = Group::create();
      $group->name = 'users';
      $group->title = tr('User');
      $group->save();
      $group->setPermission('frontend', TRUE);

      $group = Group::create();
      $group->name = 'guests';
      $group->title = tr('Guest');
      $group->save();
      $group->setPermission('frontend', TRUE);
    }

    $this->m->Configuration->setDefault(array(
      'authentication.defaultGroups.unregistered' => 'guests',
      'authentication.defaultGroups.registered' => 'users'
    ));
    
    if (!$this->isLoggedIn()) {
      $unregistered = Group::first(SelectQuery::create()
        ->where('name = ?', $this->m->Configuration['authentication.defaultGroups.unregistered'])
      );
      if ($unregistered) {
        $this->unregistered = $unregistered;
      }
    }

    if ($this->m->Actions->has('logout')) {
      $this->logOut();
      $this->m->Http->refreshPath();
    }

  }

  public function isLoggedIn() {
    if (isset($this->user)) {
      return TRUE;
    }
    if ($this->checkSession()) {
      return TRUE;
    }
    if ($this->checkCookie()) {
      return TRUE;
    }
    return FALSE;
  }

  public function hasPermission($permission) {
    if ($this->isLoggedIn()) {
      return $this->user->hasPermission($permission);
    }
    else if (isset($this->unregistered)) {
      return $this->unregistered->hasPermission($permission);
    }
    else {
      return FALSE;
    }
  }
  
  public function getUser() {
    return $this->isLoggedIn() ? $this->user : FALSE;
  }

  protected function checkSession() {
    if (isset($_SESSION[SESSION_PREFIX . 'username'])) {
      $sid = session_id();
      $ip = $_SERVER['REMOTE_ADDR'];
      $username = $_SESSION[SESSION_PREFIX . 'username'];
      $user = User::first(
        SelectQuery::create()
          ->where('username = ? AND session = ? AND ip = ?')
          ->addVar($username)
          ->addVar($sid)
          ->addVar($ip)
      );
      if ($user) {
        $this->user = $user;
        return TRUE;
      }
    }
    return FALSE;
  }

  protected function checkCookie() {
    if (isset($_COOKE[SESSION_PREFIX . 'login'])) {
      list($username, $cookie) = explode(':', $_COOKIE[SESSION_PREFIX . 'login']);
      $user = User::first(
          SelectQuery::create()
          ->where('username = ? AND cookie = ?')
          ->addVar($username)
          ->addVar($cookie)
      );
      if ($user) {
        $this->user = $user;
        return TRUE;
      }
      else {
        setcookie(SESSION_PREFIX . 'login', '', time(), WEBPATH);
        unset($_COOKIE[SESSION_PREFIX . 'login']);
      }
    }
    return FALSE;
  }

  protected function setSession($remember = FALSE) {
    session_regenerate_id();
    $sid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = $this->user->username;
    $cookie = $this->user->cookie;
    $_SESSION[SESSION_PREFIX . 'username'] = $username;
    if ($remember) {
      $cookie = md5($username . rand() . time());
      $cookieval = implode(':', array($username, $cookie));
      setcookie(SESSION_PREFIX . 'login', $cookieval, time()+60*60*24*365, WEBPATH);
    }
    $this->user->session = $sid;
    $this->user->cookie = $cookie;
    $this->user->ip = $ip;
    if (!$this->user->save(array('validate' => FALSE))) {
      throw new Exception(tr('Could not save user session data.'));
    }

  }

  public function logIn($username, $password, $remember = FALSE) {
    $user = User::first(
      SelectQuery::create()
        ->where('username = ?')
        ->addVar($username)
    );
    if (!$user) {
      return FALSE;
    }
    if (!$this->m->Shadow->compare($password, $user->password)) {
      return FALSE;
    }
    $this->user = $user;
    $this->setSession($remember);
    return TRUE;
  }

  public function logOut() {
    $this->sessionDefaults();
    if (isset($_COOKIE[SESSION_PREFIX . 'login'])) {
      setcookie(SESSION_PREFIX . 'login', '', time(), WEBPATH);
      unset($_COOKIE[SESSION_PREFIX . 'login']);
    }
    $this->user = NULL;
  }

  protected function sessionDefaults() {
    $_SESSION[SESSION_PREFIX . 'username'] = '';
  }
}
