<?php
// Module
// Name           : Authentication
// Version        : 0.2.0
// Description    : The PeanutCMS authentication system
// Author         : PeanutCMS
// Dependencies   : errors configuration templates actions database routes http

/**
 * Authentication module
 *
 * @package PeanutCMS
 */

/**
 * Authentication class
 */
class Authentication extends ModuleBase {

  private $user;

  private $hashTypes = array(
    'sha512',
    'sha256',
    'blowfish',
    'md5',
    'ext_des',
    'std_des'
  );

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
      $group->setPermission('content.comments.create', TRUE);

      $group = Group::create();
      $group->name = 'guests';
      $group->title = tr('Guest');
      $group->save();
      $group->setPermission('content.comments.create', TRUE);
    }

    $this->m->Configuration->setDefault(array(
      'authentication.defaultGroups.unregistered' => 'guests',
      'authentication.defaultGroups.registered' => 'users'
    ));

    if (!$this->m->Configuration->exists('authentication.hashType')) {
      foreach ($this->hashTypes as $hashType) {
        $constant = 'CRYPT_' . strtoupper($hashType);
        if (defined($constant) AND constant($constant) == 1) {
          $this->m->Configuration->set('authentication.hashType', $hashType);
          break;
        }
      }
    }

    if ($this->m->Actions->has('logout')) {
      $this->logOut();
      $this->m->Http->refreshPath();
    }

  }

  public function genSalt($hashType = NULL) {
    if (!isset($hashType)) {
      $hashType = $this->m->Configuration->get('authentication.hashType');
      if ($hashType == 'auto') {
        foreach ($this->hashTypes as $t) {
          $constant = 'CRYPT_' . strtoupper($t);
          if (defined($constant) AND constant($constant) == 1) {
            $hashType = $t;
          }
        }
      }
    }
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
    $max = strlen($chars) - 1;
    $salt = '';
    switch (strtolower($hashType)) {
      case 'sha512':
        $saltLength = 16;
        // rounds from 1000 to 999,999,999
        $prefix = '$6$rounds=5000$';
        break;
      case 'sha256':
        $saltLength = 16;
        // rounds from 1000 to 999,999,999
        $prefix = '$5$rounds=5000$';
        break;
      case 'blowfish':
        $saltLength = 22;
        // cost (second param) from 04 to 31
        $prefix = '$2a$08$';
        break;
      case 'md5':
        $saltLength = 8;
        $prefix = '$1$';
        break;
      case 'ext_des':
        $saltLength = 4;
        // iterations (4 characters after _) from .... to zzzz
        $prefix = '_J9..';
        break;
      case 'std_des':
      default:
        $saltLength = 2;
        $prefix = '';
        break;
    }
    for ($i = 0; $i < $saltLength; $i++) {
      $salt .= $chars[mt_rand(0, $max)];
    }
    return $prefix . $salt;
  }

  public function hash($string, $hashType = NULL) {
    return crypt($string, $this->genSalt($hashType));
  }

  public function compareHash($string, $hash) {
    return crypt($string, $hash) == $hash;
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
    if (!$this->user->save()) {
      var_dump($this->user->getErrors());
      exit;
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
    if (!$this->compareHash($password, $user->password)) {
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
