<?php
/*
 * Static pages
 *
 * @package PeanutCMS
 */

/**
 * Pages class
 */
class Users implements IModule{

  private $errors;
  private $configuration;
  private $actions;
  private $database;
  private $routes;
  private $templates;
  private $http;

  public function getConfiguration() {
    return $this->configuration;
  }

  public function getErrors() {
    return $this->errors;
  }

  public function getActions() {
    return $this->actions;
  }

  public function getHttp() {
    return $this->http;
  }

  public function getDatabase() {
    return $this->database;
  }

  public function getRoutes() {
    return $this->routes;
  }

  public function getTemplates() {
    return $this->templates;
  }

  private $user;

  public function __construct(Database $database) {
    $this->database = $database;
    $this->actions = $this->database->getActions();
    $this->routes = $this->database->getRoutes();
    $this->http = $this->routes->getHttp();
    $this->templates = $this->routes->getTemplates();
    $this->errors = $this->routes->getErrors();
    $this->configuration = $this->database->getConfiguration();

    if (!ActiveRecord::isConnected()) {
      throw new Exception('temporary.');
    }

    $newInstall = FALSE;

    require_once(p(MODELS . 'user.class.php'));

    if (!$this->database->tableExists('users')) {
      $this->database->createQuery('users')
        ->addInt('id', TRUE, TRUE)
        ->setPrimaryKey('id')
        ->addVarchar('username', 255)
        ->addVarchar('password', 255)
        ->addVarchar('email', 255)
        ->addVarchar('session', 255)
        ->addVarchar('cookie', 255)
        ->addVarchar('ip', 255)
        ->addInt('group_id', TRUE)
        ->addIndex(TRUE, 'username')
        ->addIndex(TRUE, 'email')
        ->execute();
    }

    ActiveRecord::addModel('User', 'users');

    require_once(p(MODELS . 'group.class.php'));

    if (!$this->database->tableExists('groups')) {
      $this->database->createQuery('groups')
      ->addInt('id', TRUE, TRUE)
      ->setPrimaryKey('id')
      ->addVarchar('name', 255)
      ->addVarchar('title', 255)
      ->addIndex(TRUE, 'name')
      ->execute();
      $newInstall = TRUE;
    }

    ActiveRecord::addModel('Group', 'groups');

    if (!$this->database->tableExists('groups_permissions')) {
      $this->database->createQuery('groups_permissions')
      ->addInt('group_id', TRUE, TRUE)
      ->addVarchar('permission', 255)
      ->setPrimaryKey('group_id', 'permission')
      ->execute();
    }

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

    if (!$this->configuration->exists('users.defaultGroups.unregistered')) {
      $this->configuration->set('users.defaultGroups.unregistered', 'guests');
    }

    if (!$this->configuration->exists('users.defaultGroups.registered')) {
      $this->configuration->set('users.defaultGroups.registered', 'users');
    }

    if ($this->actions->has('logout')) {
      $this->logOut();
      $this->http->refreshPath();
    }

  }

  public static function getDependencies() {
    return array('database');
  }

  public function getLink(User $record) {
    return $this->http->getLink($record->getPath());
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
    $this->user->save();

  }

  public function logIn($username, $password, $remember = FALSE) {
    $user = User::first(
      SelectQuery::create()
        ->where('username = ? AND password = ?')
        ->addVar($username)
        ->addVar(sha1($password))
    );
    if (!$user) {
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