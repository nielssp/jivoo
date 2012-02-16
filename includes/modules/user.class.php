<?php
/*
 * Class for logging in
 *
 * @package PeanutCMS
 */

/**
 * User class
 */
class User {

  /**
   * Is the user logged in?
   * @var bool
   */
  var $loggedIn = false;

  /**
   * Login error
   * @var string
   */
  var $loginError;

  /**
   * Username used for attempted login
   * @var string
   */
  var $loginUsername;


  /**
   * Constructor
   */
  function User() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    global $PEANUT;
    //Define templates
    $PEANUT['templates']->defineTemplate('login', array($this, 'getPath'), array($this, 'getTitle'));

    // Set default settings
    if (!$PEANUT['configuration']->exists('loginPermalink') OR $PEANUT['configuration']->get('loginPermalink') == '')
      $PEANUT['configuration']->set('loginPermalink', 'admin');
    
    /**
     * @todo Installation:
     */
    if (!$PEANUT['configuration']->exists('adminUsername'))
      $PEANUT['configuration']->set('adminUsername', 'admin');
    if (!$PEANUT['configuration']->exists('adminPassword'))
      $PEANUT['configuration']->set('adminPassword', sha1('admin'));

    
    if (!isset($_SESSION[SESSION_PREFIX . 'username']))
      $this->sessionDefaults();
    $this->isLoggedIn();
    if ($PEANUT['actions']->has('logout')) {
      $this->logOut();
      $PEANUT['http']->refreshPath();
    }
    if ($PEANUT['actions']->has('login')) {
      $username = $_POST['username'];
      $this->loginUsername = htmlentities($username, ENT_COMPAT, 'UTF-8');
      $password = $_POST['password'];
      $remember = false;
      if ($_POST['remember'] == 'remember')
        $remember = true;
      if ($this->logIn($username, $password, $remember))
        $PEANUT['http']->refreshPath();
      else
        $this->loginError = tr('Wrong username or password');
    }

    // Detect permalink
    $this->detect();
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  function detect() {
    global $PEANUT;
    $path = $PEANUT['http']->path;
    if (!is_array($path))
      return;
    if ($path[0] != $PEANUT['configuration']->get('loginPermalink'))
      return;
    if ($this->loggedIn)
      $PEANUT['http']->redirectPath(array(), false);
    else
      $PEANUT['templates']->setTemplate('login', 10);
  }

  function getPath($template, $parameters = array()) {
    global $PEANUT;
    switch ($template) {
      case 'login':
        return array($PEANUT['configuration']->get('loginPermalink'));
        break;
      default:
        break;

    }
  }

  function getTitle($template, $parameters = array()) {
    global $PEANUT;
    switch ($template) {
      case 'login':
        return tr('Log in');
        break;
      default:
        break;

    }
  }

  /**
   * Create the sessions and set their values if they do not exist.
   */
  function sessionDefaults() {
    $_SESSION[SESSION_PREFIX . 'username'] = '';
  }

  /**
   * Checks if a user is logged in.
   *
   * @return bool True if the user is logged in
   */
  function isLoggedIn() {
    if ($this->loggedIn)
      return true;
    if ($this->checkSession())
      return true;
    if ($this->checkCookie())
      return true;
    return false;
  }

  /**
   * Try to log in with a username or password
   *
   * @param string $username Username
   * @param string $password Password
   * @param bool $remember Create cookie? Default is false.
   * @return bool True if successful
   */
  function logIn($username, $password, $remember = false) {
    global $PEANUT;
    if ($this->isLoggedIn()) {
      return true;
    }
    else {
      $password = sha1($password);
      if ($PEANUT['configuration']->get('adminUsername') == $username AND $PEANUT['configuration']->get('adminPassword') == $password) {
        $this->setSession($remember);
        $this->loggedIn = true;
        return true;
      }
    }
    return false;
  }

  /**
   * Log out and unset cookie.
   *
   * @return void
   */
  function logOut() {
    $this->sessionDefaults();
    if (isset($_COOKIE[SESSION_PREFIX . 'login'])) {
      setcookie(SESSION_PREFIX . 'login', '', time(), WEBPATH);
      unset($_COOKIE[SESSION_PREFIX . 'login']);
    }
    $this->loggedIn = false;
  }

  /**
   * Logs into user.
   *
   * @param array $userdata User data array from database.
   * @param bool $remember Create cookie? Default is false.
   * @return void
   */
  function setSession($remember = false) {
    global $PEANUT;
    session_regenerate_id();
    $this->loggedIn = true;
    $sid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = $PEANUT['configuration']->get('adminUsername');
    $cookie = $PEANUT['configuration']->get('adminCookie');
    $_SESSION[SESSION_PREFIX . 'username'] = $username;
    if ($remember) {
      $cookie = md5($username . rand() . time());
      $cookieval = implode(':', array($username, $cookie));
      setcookie(SESSION_PREFIX . 'login', $cookieval, time()+60*60*24*365, WEBPATH);
    }
    $PEANUT['configuration']->set('adminSession', $sid);
    $PEANUT['configuration']->set('adminIp', $ip);
    $PEANUT['configuration']->set('adminCookie', $cookie);
  }

  /**
   * Checks the sessions to see if a user is logged in
   *
   * @return bool True if logged in
   */
  function checkSession() {
    global $PEANUT;
    if (isset($_SESSION[SESSION_PREFIX . 'username'])) {
      $sid = session_id();
      $ip = $_SERVER['REMOTE_ADDR'];
      $username = $_SESSION[SESSION_PREFIX . 'username'];
      if ($PEANUT['configuration']->get('adminUsername') == $username AND
              $PEANUT['configuration']->get('adminSession') == $sid AND
              $PEANUT['configuration']->get('adminIp') == $ip) {
        $this->loggedIn = true;
        return true;
      }
    }
    return false;
  }

  /**
   * Checks for cookie
   *
   * @return bool True if logged in
   */
  function checkCookie() {
    global $PEANUT;
    if (isset($_COOKIE[SESSION_PREFIX . 'login'])) {
      list($username, $cookie) = explode(':', $_COOKIE[SESSION_PREFIX . 'login']);
      if ($PEANUT['configuration']->get('adminUsername')  == $username AND $PEANUT['configuration']->get('adminCookie')  == $cookie) {
        $this->loggedIn = true;
        $this->setSession($this->data, true);
        return true;
      }
      else {
        setcookie(SESSION_PREFIX . 'login', '', time(), WEBPATH);
        unset($_COOKIE[SESSION_PREFIX . 'login']);
      }
    }
    return false;
  }

}
