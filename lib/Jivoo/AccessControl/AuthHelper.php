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
  
  /**
   * @var IAuthentication[]
   */
  private $authenticationMethods = array();

  /**
   * @var IAuthorization[]
   */
  private $authorizationMethods = array();

  public function allow($permission) {}

  public function isLoggedIn() {
    return isset($this->user) or $this->checkSession() or $this->checkCookie();
  }

  public function isAllowed() {
    return false;
  }

  /**
   * Check session for logged in user
   * @return boolean True if logged in, false otherwise
   */
  private function checkSession() {
    if (isset($this->session['auth_session'])) {
      $sessionId = $this->session['auth_session'];
      $user = $this->userModel->openSession($sessionId);
      if ($user) {
        $this->user = $user;
        if (isset($this->session['auth_renew_at'])) {
          if ($this->session['auth_renew_at'] <= time()) {
            $this->session['auth_renew_at'] = time() + $this->renewSessionAfter;
            $this->userModel->renewSession($sessionId, 
              time() + $this->sessionLifetime);
          }
        }
        return true;
      }
      unset($this->session['auth_session']);
    }
    return false;
  }

  /**
   * Check cookie for logged in user
   * @return boolean True if logged in, false otherwise
   */
  private function checkCookie() {
    if (isset($this->request->cookies['auth_session'])) {
      $sessionId = $this->request->cookies['auth_session'];
      $user = $this->userModel->openSession($sessionId);
      if ($user) {
        $this->user = $user;
        return true;
      }
      unset($this->request->cookies['auth_session']);
    }
    return false;
  }

  public function getUser() {
    return $this->user;
  }

  public function logIn($data = null) {
    foreach ($this->authenticationMethods as $method) {
      $user = $method->authenticate($this->request, $this->userModel);
      if ($user != null) {
        $this->user = $user;
        break;
      }
    }
    if (!isset($this->user))
      return false;
    $validUntil = time() + 60 * 60;
    $sessionId = $this->userModel->createSession($this->user, $validUntil);
    $this->session['auth_session_id'] = $sessionId;
    $this->session['auth_renew_at'] = time() + 60 * 30;
    return true;
  }

  public function logOut() {
  	if ($this->isLoggedIn()) {
  	  $this->user = null;
  	}
  }
}
