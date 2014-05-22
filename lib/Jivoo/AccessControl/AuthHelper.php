<?php
/**
 * Helper class for authentication and autorization
 * @package Jivoo\AccessControl
 */
class AuthHelper extends Helper {

  protected $modules = array('AccessControl');

  private $user = null;
  
  private $sessionId = null;

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
  
  private $permissionPrefix = '';
  
  private $loginRoute = null;
  private $unauthorizedRoute = null;
  private $ajaxRoute = null;
  
  /**
   * @var IAuthentication[]
   */
  private $authenticationMethods = array();

  /**
   * @var IAuthorization[]
   */
  private $authorizationMethods= array();
  
  /**
   * @var IAcl[]
   */
  private $aclMethods = array();
  
  private $passwordHasher = null;
  
  private $defaultAcl = null;
  
  protected function init() {
    $this->passwordHasher = $this->m->AccessControl->getPasswordHasher();
    $this->m->Routing->attachEventHandler('beforeCallAction', array($this, 'checkAuthorization'));
    $this->defaultAcl = new DefaultAcl($this->app);
    $this->addAcl($this->defaultAcl);
  }

  public function __get($property) {
    switch ($property) {
      case 'userModel':
      case 'loginRoute':
      case 'unauthorizedRoute':
      case 'ajaxRoute':
      case 'sessionId':
      case 'createSessions':
      case 'createCookies':
      case 'sessionPrefix':
      case 'sessionLifeTime':
      case 'sessionRenewAfter':
      case 'cookiePrefix':
      case 'cookieLifeTime':
      case 'cookieRenewAfter':
      case 'passwordHasher':
      case 'permissionPrefix':
        return $this->$property;
      case 'user':
        return $this->getUser();
    }
  }

  public function __set($property, $value) {
    switch ($property) {
      case 'userModel':
      case 'loginRoute':
      case 'unauthorizedRoute':
      case 'ajaxRoute':
      case 'createSessions':
      case 'createCookies':
      case 'sessionPrefix':
      case 'sessionLifeTime':
      case 'sessionRenewAfter':
      case 'cookiePrefix':
      case 'cookieLifeTime':
      case 'cookieRenewAfter':
      case 'permissionPrefix':
        $this->$property = $value;
        break;
      case 'passwordHasher':
        if ($value instanceof IPasswordHasher)
          $this->passwordHasher = $value;
        else
          $this->passwordHasher = $this->m->AccessControl->getPasswordHasher($value);
        break;
      case 'authentication':
        if (is_array($value)) {
          foreach ($value as $name => $options) {
            if (!is_string($name)) {
              $name = $options;
              $options = array();
            }
            $this->loadAuthentication($name, $options);
          }
        }
        else {
          $this->loadAuthentication($value);
        }
        break;
      case 'authorization':
        if (is_array($value)) {
          foreach ($value as $name => $options) {
            if (!is_string($name)) {
              $name = $options;
              $options = array();
            }
            $this->loadAuthorization($name, $options);
          }
        }
        else {
          $this->loadAuthorization($value);
        }
        break;
      case 'acl':
        if (is_array($value)) {
          foreach ($value as $name => $options) {
            if (!is_string($name)) {
              $name = $options;
              $options = array();
            }
            $this->loadAcl($name, $options);
          }
        }
        else {
          $this->loadAcl($value);
        }
        break;
    }
  }
  
  private function loadAuthentication($name, $options = array()) {
    if ($name instanceof IAuthentication)
      return $this->addAuthentication($name);
    $name = $name . 'Authentication';
    Lib::assumeSubclassOf($name, 'LoadableAuthentication');
    $this->addAuthentication(new $name($this->app, $options));
  }
  
  private function loadAuthorization($name, $options = array()) {
    if ($name instanceof IAuthorization)
      return $this->addAuthorization($name);
    $name = $name . 'Authorization';
    Lib::assumeSubclassOf($name, 'LoadableAuthorization');
    $this->addAuthorization(new $name($this->app, $options, $this));
  }
  
  private function loadAcl($name, $options = array()) {
    if ($name instanceof IAcl)
      return $this->addAcl($name);
    $name = $name . 'Acl';
    Lib::assumeSubclassOf($name, 'LoadableAcl');
    $this->addAcl(new $name($this->app, $options));
  }
  
  public function addAuthentication(IAuthentication $authentication) {
    $this->authenticationMethods[] = $authentication;
  }
  
  public function addAuthorization(IAuthorization $authorization) {
    $this->authorizationMethods[] = $authorization;
  }
  
  public function addAcl(IAcl $acl) {
    $this->aclMethods[] = $acl;
  }
  
  public function allow($permission = null) {
    $this->defaultAcl->allow($permission);
  }
  
  public function deny($permission = null) {
    $this->defaultAcl->deny($permission);
  }

  public function isLoggedIn() {
    return isset($this->user)
      or $this->checkSession() or $this->checkCookie();
  }
  
  public function check($permission) {
    if (!$this->hasPermission($permission))
      $this->authorizationError();
  }
  
  public function hasPermission($permission) {
    return $this->checkAcl($this->permissionPrefix . $permission);
  }
  
  private function checkAcl($permission) {
    foreach ($this->aclMethods as $method) {
      if ($method->hasPermission($this->user, $permission))
        return true;
    }
    if (strpos($permission, '.') !== false)
      return $this->hasPermission(preg_replace('/\\..+?$/', '', $permission));
    return false;
  }
  
  public function authenticationError() {
    if ($this->request->isAjax() and isset($this->ajaxRoute))
      $this->m->Routing->redirect($this->ajaxRoute);
    if (isset($this->loginRoute))
      $this->m->Routing->redirect($this->loginRoute);
    throw new ResponseOverrideException(new TextResponse(403, 'text/plain', tr('Unauthenticated')));
  }
  
  public function authorizationError() {
    if (!$this->isLoggedIn())
      $this->authenticationError();
    if ($this->request->isAjax() and isset($this->ajaxRoute))
      $this->m->Routing->redirect($this->ajaxRoute);
    if (isset($this->unauthorizedRoute))
      $this->m->Routing->redirect($this->unauthorizedRoute);
    if (isset($this->loginRoute))
      $this->m->Routing->redirect($this->loginRoute);
    throw new ResponseOverrideException(new TextResponse(403, 'text/plain', tr('Unauthorized')));
  }
  
  public function checkAuthorization(CallActionEvent $event) {
    if (empty($this->authorizationMethods))
      return;
    $authRequest = new AuthorizationRequest($event->controller, $event->action, $this->user);
    foreach ($this->authorizationMethods as $method) {
      if ($method->authorize($authRequest))
        return;
    }
    $this->authorizationError();
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
        $this->sessionId = $sessionId;
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
        $this->sessionId = $sessionId;
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
    if (!isset($this->user))
      $this->isLoggedIn();
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
      $user = $method->authenticate($data, $this->userModel, $this->passwordHasher);
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
      $this->sessionId = $sessionId;
    }
    else {
      $validUntil = time() + $this->sessionLifeTime;
      $sessionId = $this->userModel->createSession($this->user, $validUntil);
      $this->session[$this->sessionPrefix . 'session'] = $sessionId;
      $this->session[$this->sessionPrefix . 'renew_at'] = time() + $this->sessionRenewAfter;
      $this->sessionId = $sessionId;
    }
    return true;
  }

  public function logOut() {
  	if ($this->isLoggedIn()) {
  	  if (isset($this->sessionId)) {
  	    $this->userModel->deleteSession($this->sessionId);
  	    unset($this->sessionId);
  	  }
  	  if (isset($this->session[$this->sessionPrefix . 'session'])) {
  	    unset($this->session[$this->sessionPrefix . 'session']);
  	    unset($this->session[$this->sessionPrefix . 'renew_at']);
  	  }
  	  if (isset($this->request->cookies[$this->cookiePrefix . 'session'])) {
  	    unset($this->request->cookies[$this->cookiePrefix . 'session']);
  	    unset($this->request->cookies[$this->cookiePrefix . 'renew_at']);
  	  }
      foreach ($this->authenticationMethods as $method) {
        $method->deauthenticate($this->user, $this->userModel);
      }
  	  unset($this->user);
  	}
  }
}
