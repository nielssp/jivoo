<?php
/**
 * Helper class for authentication and autorization
 * @package Jivoo\AccessControl
 */
class AuthHelper extends Helper {

  protected $modules = array('AccessControl');

  private $user = null;
  private $userModel = null;
  
  public function allow($permission) {
    
  }
  
  public function isLoggedIn() {
    return isset($this->user)
      or $this->checkSession()
      or $this->checkCookie();
  }
  
  /**
   * Check session for logged in user
   * @return boolean True if logged in, false otherwise
   */
  protected function checkSession() {
    if (isset($this->session['auth_session'])) {
      $sessionId = $this->session['auth_session'];
      $user = $this->userModel->openSession($sessionId);
      if ($user) {
        $this->user = $user;
        if (isset($this->session['auth_renew_at'])) {
          if ($this->session['auth_renew_at'] <= time()) {
            $this->session['auth_renew_at'] = time() + $this->renewSessionAfter;
            $this->userModel->renewSession($sessionId, $time() + $this->sessionLifetime);
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
  protected function checkCookie() {
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
  
  public function logIn() {
    
  }
  
  public function logOut() {
    
  }
  
}
