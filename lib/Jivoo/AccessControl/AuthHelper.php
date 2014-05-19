<?php
/**
 * Helper class for authentication and autorization
 * @package Jivoo\AccessControl
 */
class AuthHelper extends Helper {

  protected $modules = array('AccessControl');

  private $user = null;

  /**
   * @var IUserModel
   */
  private $userModel = null;
  
  private $createSessions = true;
  private $sessionPrefix = 'auth_';
  private $sessionLifeTime = 3600; // 1 hour
  private $sessionRenewAfter = 1800; // 0.5 hours

  private $createCookies = true;
  private $cookiePrefix = 'auth_';
  private $cookieLifeTime = 2592000; // 30 days
  private $cookieRenewAfter = 864000; // 10 days
  
  /**
   * @var IAuthentication[]
   */
  private $authenticationMethods = array();

  /**
   * @var IAuthorization[]
   */
  private $authorizationMethods= array();

  public function __get($property) {
    switch ($property) {
      case 'userModel':
      case 'user':
      case 'createSessions':
      case 'createCookies':
      case 'sessionPrefix':
      case 'sessionLifeTime':
      case 'sessionRenewAfter':
      case 'cookiePrefix':
      case 'cookieLifeTime':
      case 'cookieRenewAfter':
        return $this->$property;
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'userModel':
      case 'createSessions':
      case 'createCookies':
      case 'sessionPrefix':
      case 'sessionLifeTime':
      case 'sessionRenewAfter':
      case 'cookiePrefix':
      case 'cookieLifeTime':
      case 'cookieRenewAfter':
        $this->$property = $value;
        break;
      case 'authentication':
        if (is_array($value)) {
          foreach ($value as $name => $options) {
            if (!is_string($name)) {
              $name = $options;
              $options = array();
            }
            if ($name instanceof IAuthentication)
              $this->addAuthentication($name);
            else
              $this->loadAuthentication($name, $options);
          }
        }
        else if ($value instanceof IAuthentication) {
          $this->addAuthentication($value);
        }
        else {
          $this->loadAuthentication($value);
        }
        break;
    }
  }
  
  private function loadAuthentication($name, $options = array()) {
    $name = $name . 'Authentication';
    if (!is_subclass_of($name, 'LoadableAuthentication')) {
      throw new Exception();
    }
    $this->addAuthentication(new $name($this->app, $options));
  }
  
  public function addAuthentication(IAuthentication $authentication) {
    $this->authenticationMethods[] = $authentication;
  }

  public function allow($permission) {}

  public function isLoggedIn() {
    return isset($this->user) or $this->getUser() != null
      or $this->checkSession() or $this->checkCookie();
  }

  public function isAllowed() {
    return false;
  }

  /**
   * Check session for logged in user
   * @return boolean True if logged in, false otherwise
   */
  private function checkSession() {
    if (isset($this->session[$this->sessionPrefix . 'session'])) {
      $sessionId = $this->session[$this->sessionPrefix . 'session'];
      $user = $this->userModel->openSession($sessionId);
      if ($user) {
        $this->user = $user;
        if (isset($this->session[$this->sessionPrefix . 'renew_at'])) {
          if ($this->session[$this->sessionPrefix . 'renew_at'] <= time()) {
            $this->session[$this->sessionPrefix . 'renew_at'] = time() + $this->sessionRenewAfter;
            $this->userModel->renewSession($sessionId, 
              time() + $this->sessionLifetime);
          }
        }
        return true;
      }
      unset($this->session[$this->sessionPrefix . 'session']);
    }
    return false;
  }

  /**
   * Check cookie for logged in user
   * @return boolean True if logged in, false otherwise
   */
  private function checkCookie() {
    if (isset($this->request->cookies[$this->cookiePrefix . 'session'])) {
      $sessionId = $this->request->cookies[$this->cookiePrefix . 'session'];
      $user = $this->userModel->openSession($sessionId);
      if ($user) {
        $this->user = $user;
        if (isset($this->request->cookies[$this->cookiePrefix . 'renew_at'])) {
          if ($this->request->cookies[$this->cookiePrefix . 'renew_at'] <= time()) {
            $this->createCookie($sessionId);
            $this->userModel->renewSession($sessionId, 
              time() + $this->cookieLifeTime);
          }
        }
        return true;
      }
      unset($this->request->cookies[$this->cookiePrefix . 'session']);
    }
    return false;
  }

  public function getUser() {
    return $this->user;
  }
  
  private function createCookie($sessionId) {
    $this->request->cookies->setCookie(
      $this->cookiePrefix . 'session',
      $sessionId, time() + $this->cookieLifeTime
    );
    if ($this->cookieRenewAfter >= 0) {
      $this->request->cookies->setCookie(
        $this->cookiePrefix . 'renew_at',
        time() + $this->cookieRenewAfter, time() + $this->cookieLifeTime
      );
    }
  }

  public function logIn($data = null) {
    if (!isset($data))
      $data = $this->request->data;
    $cookie = false;
    foreach ($this->authenticationMethods as $method) {
      $user = $method->authenticate($data, $this->userModel);
      if ($user != null) {
        $this->user = $user;
        $cookie = $method->cookie();
        break;
      }
    }
    if (!isset($this->user))
      return false;
    if ($cookie) {
      $validUntil = time() + $this->cookieLifeTime;
      $sessionId = $this->userModel->createSession($this->user, $validUntil);
      $this->createCookie($sessionId);
    }
    else {
      $validUntil = time() + $this->sessionLifeTime;
      $sessionId = $this->userModel->createSession($this->user, $validUntil);
      $this->session[$this->sessionPrefix . 'session'] = $sessionId;
      $this->session[$this->sessionPrefix . 'renew_at'] = time() + $this->sessionRenewAfter;
    }
    return true;
  }

  public function logOut() {
  	if ($this->isLoggedIn()) {
  	  $this->user = null;
  	}
  }
}
