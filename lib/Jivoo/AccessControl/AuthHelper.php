<?php
/**
 * Helper class for authentication and autorization.
 * @package Jivoo\AccessControl
 * @property IUserModel $userModel User model.
 * @property array|ILinkable|string|null $loginRoute Route for login page, ee
 * {@see Routing}.
 * @property array|ILinkable|string|null $unauthorizedRoute Route to redirect to
 * when user unauthorized, see {@see Routing}.
 * @property array|ILinkable|string|null $ajaxRoute Route to redirect to for
 * AJAX requests,  see {@see Routing}.
 * @property-read string $sessionId Current session id.
 * @property bool $createSessions Whether or not to create sessions.
 * @property string $sessionPrefix Session prefix.
 * @property int $sessionLifeTime Life time of session in seconds.
 * @property int $sessionRenewAfter Number of seconds after which the session
 * is renewed.
 * @property bool $createCookies Whether or not to create cookies.
 * @property string $cookiePrefix Cookie prefix.
 * @property int $cookieLifeTime Life time of cookies in seconds.
 * @property int $cookieRenewAfter Number of seconds after which the cookie
 * is renewed.
 * @property IPasswordHasher $passwordHasher Password hasher used to hash
 * passwords.
 * @property string $permissionPrefix Prefix for permissions when checking
 * access control lists.
 * @property-read ActiveRecord $user Current user if logged in.
 * @property-write string|IAuthentication|string[]|IAuthentication[]|array[] $authentication
 * Add one or more access control lists. Can be the name of a class (without
 * 'Authentication'-suffix) or a list of names, see
 * {@see LoadableAuthentication}. Can also be an associative array mapping names
 * to options.
 * @property-write string|IAuthorization|string[]|IAuthorization[]|array[] $authorization
 * Add one or more access control lists. Can be the name of a class (without
 * 'Authorization'-suffix) or a list of names, see {@see LoadableAuthorization}.
 * Can also be an associative array mapping names to options.
 * @property-write string|IAcl|string[]|IAcl[]|array[] $acl
 * Add one or more access control lists. Can be the name of a class (without
 * 'Acl'-suffix) or a list of names, see {@see LoadableAcl}. Can also be an
 * associative array mapping names to options.
 */
class AuthHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('AccessControl');

  /**
   * @var ActiveRecord Current user, if logged in. 
   */
  private $user = null;
  
  /**
   * @var string Current session id.
   */
  private $sessionId = null;

  /**
   * @var IUserModel User model.
   */
  private $userModel = null;
  
  /**
   * @var bool Whether or not to create sessions.
   */
  private $createSessions = true;
  
  /**
   * @var string Session prefix.
   */
  private $sessionPrefix = 'auth_';
  
  /**
   * @var int Life time of session.
   */
  private $sessionLifeTime = 3600; // 1 hour
  
  /**
   * @var int Time after which session is renewed.
   */
  private $sessionRenewAfter = 1800; // 0.5 hours

  /**
   * @var bool Whether or not to create cookies.
   */
  private $createCookies = true;
  
  /**
   * @var string Cookie prefix.
   */
  private $cookiePrefix = 'auth_';
  
  /**
   * @var int Life time of cookie.
   */
  private $cookieLifeTime = 2592000; // 30 days
  
  /**
   * @var int Time after which cookie is renewed.
   */
  private $cookieRenewAfter = 864000; // 10 days
  
  /**
   * @var string Prefix for permissions.
   */
  private $permissionPrefix = '';
  
  /**
   * @param array|ILinkable|string|null $route Route for login page.
   */
  private $loginRoute = null;
  
  /**
   * @param array|ILinkable|string|null $route Route unauthorized page.
   */
  private $unauthorizedRoute = null;
  
  /**
   * @var array|ILinkable|string|null $route Route for AJAX requests.
   */
  private $ajaxRoute = null;
  
  /**
   * @var IAuthentication[] Associative array of authentication methods.
   */
  private $authenticationMethods = array();
  
  /**
   * @var IAuthentication[] Associative array of stateless authentication methods.
   */
  private $stateLessAuthenticationMethods = array();

  /**
   * @var IAuthorization[] Associative array of authorization methods.
   */
  private $authorizationMethods= array();
  
  /**
   * @var IAcl[] Associative array of ACL handlers.
   */
  private $aclMethods = array();
  
  /**
   * @var IPasswordHasher Password hasher.
   */
  private $passwordHasher = null;
  
  /**
   * @var DefaultAcl Default access control list.
   */
  private $defaultAcl = null;
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->passwordHasher = $this->m->AccessControl->getPasswordHasher();
    $this->m->Routing->attachEventHandler('beforeCallAction', array($this, 'checkAuthorization'));
    $this->defaultAcl = new DefaultAcl($this->app);
    $this->addAcl($this->defaultAcl);
  }

  /**
   * {@inheritdoc}
   */
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
    return parent::__get($property);
  }

  /**
   * {@inheritdoc}
   */
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
        return;
      case 'passwordHasher':
        if ($value instanceof IPasswordHasher)
          $this->passwordHasher = $value;
        else
          $this->passwordHasher = $this->m->AccessControl->getPasswordHasher($value);
        return;
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
        return;
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
        return;
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
        return;
    }
    return parent::__set($property, $value);
  }
  
  /**
   * Load authentication module.
   * @param string|IAuthentication $name Name or object.
   * @param array $options Options for new object.
   */
  private function loadAuthentication($name, $options = array()) {
    if ($name instanceof IAuthentication)
      return $this->addAuthentication($name);
    $name = $name . 'Authentication';
    Lib::assumeSubclassOf($name, 'LoadableAuthentication');
    $this->addAuthentication(new $name($this->app, $options));
  }
  
  /**
   * Load authorization module.
   * @param string|IAuthorization $name Name or object.
   * @param array $options Options for new object.
   */
  private function loadAuthorization($name, $options = array()) {
    if ($name instanceof IAuthorization)
      return $this->addAuthorization($name);
    $name = $name . 'Authorization';
    Lib::assumeSubclassOf($name, 'LoadableAuthorization');
    $this->addAuthorization(new $name($this->app, $options, $this));
  }

  /**
   * Load ACL module.
   * @param string|IAcl $name Name or object.
   * @param array $options Options for new object.
   */
  private function loadAcl($name, $options = array()) {
    if ($name instanceof IAcl)
      return $this->addAcl($name);
    $name = $name . 'Acl';
    Lib::assumeSubclassOf($name, 'LoadableAcl');
    $this->addAcl(new $name($this->app, $options));
  }
  
  /**
   * Add an authentication module.
   * @param IAuthentication $authentication Module.
   */
  public function addAuthentication(IAuthentication $authentication) {
    $this->authenticationMethods[] = $authentication;
    if ($authentication->isStateLess())
      $this->stateLessAuthenticationMethods[] = $authentication;
  }
  
  /**
   * Add an authorization module.
   * @param IAuthorization $authorization Module.
   */
  public function addAuthorization(IAuthorization $authorization) {
    $this->authorizationMethods[] = $authorization;
  }
  
  /**
   * Add an ACL module.
   * @param IAcl $acl Module.
   */
  public function addAcl(IAcl $acl) {
    $this->aclMethods[] = $acl;
  }
  
  /**
   * Add permission to default access control list.
   * @param string $permission Permission string.
   */
  public function allow($permission = null) {
    $this->defaultAcl->allow($permission);
  }
  
  /**
   * Remove a permission from default access control list.
   * @param string $permission Permission string.
   */
  public function deny($permission = null) {
    $this->defaultAcl->deny($permission);
  }

  /**
   * Whether or not a user is logged in. Checks both session, cookie, and
   * stateless authentication.
   * @return boolean True if user logged in.
   */
  public function isLoggedIn() {
    return isset($this->user)
      or $this->checkSession()
      or $this->checkCookie()
      or $this->checkStateLess();
  }
  
  /**
   * Check permission. Craetes authoirzation error if the current user does
   * not have the specified permission.
   * @param string $permission Permission string.
   */
  public function check($permission) {
    if (!$this->hasPermission($permission))
      $this->authorizationError();
  }
  
  /**
   * Whether or not current user (or guest) has a permission.
   * @param string $permission Permission string.
   * @param string $prefix Prefix for permission, see also {@see $permissionPrefix}.
   * @return boolean True if user has permission, false otherwise.
   */
  public function hasPermission($permission, $prefix = null) {
    if (!isset($prefix))
      $prefix = $this->permissionPrefix;
    return $this->checkAcl($prefix . $permission);
  }
  
  /**
   * Check a permission on all access control lists.
   * @param string $permission Permission string.
   * @return boolean True if permission granted, false otherwise.
   */
  private function checkAcl($permission) {
    foreach ($this->aclMethods as $method) {
      if ($method->hasPermission($this->user, $permission))
        return true;
    }
    if (strpos($permission, '.') !== false)
      return $this->checkAcl(preg_replace('/\\..+?$/', '', $permission));
    return false;
  }
  
  /**
   * Redirect to a login page.
   * @throws ResponseOverrideException If no other route defined.
   */
  public function authenticationError() {
    if ($this->request->isAjax()) {
      if (isset($this->ajaxRoute))
        $this->m->Routing->redirect($this->ajaxRoute);
    }
    else if (isset($this->loginRoute))
      $this->m->Routing->redirect($this->loginRoute);
    throw new ResponseOverrideException(
      new TextResponse(Http::FORBIDDEN, 'text/plain', tr('Unauthenticated'))
    );
  }
  
  /**
   * Redirect to an "unauthorized" page if logged in, otherwise redirect
   * to login page.
   * @throws ResponseOverrideException If no route defined.
   */
  public function authorizationError() {
    if (!$this->isLoggedIn())
      $this->authenticationError();
    if ($this->request->isAjax()) {
      if (isset($this->ajaxRoute))
        $this->m->Routing->redirect($this->ajaxRoute);
    }
    else if (isset($this->unauthorizedRoute))
      $this->m->Routing->redirect($this->unauthorizedRoute);
    else if (isset($this->loginRoute))
      $this->m->Routing->redirect($this->loginRoute);
    throw new ResponseOverrideException(
      new TextResponse(Http::FORBIDDEN, 'text/plain', tr('Unauthorized'))
    );
  }
  
  /**
   * Check user authorization for an action. 
   * @param CallActionEvent $event The action event.
   */
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
              time() + $this->sessionLifeTime);
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
  
  /**
   * Check stateless authentication methods if any.
   * @return boolean True if logged in, false otherwise.
   */
  public function checkStateLess() {
    foreach ($this->stateLessAuthenticationMethods as $method) {
      $user = $method->authenticate($this->request->data, $this->userModel, 
        $this->passwordHasher);
      if ($user != null) {
        $this->user = $user;
        return true;
      }
    }
    return false;
  }

  /**
   * Get current user if logged in.
   * @return ActiveRecord Record of current user, null if not logged in.
   */
  public function getUser() {
    if (!isset($this->user))
      $this->isLoggedIn();
    return $this->user;
  }
  
  /**
   * Create long-lived session cookie.
   * @param string $sessionId Session id.
   */
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

  /**
   * Create a session for user.
   * @param ActiveRecord $user User to create session for.
   * @param string $cookie Whether or not to make the session long-lived, i.e.
   * remember the user for the next visit.
   */
  public function createSession(ActiveRecord $user, $cookie = false) {
    $this->user = $user;
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
  }

  /**
   * Use available authentication methods to log in.
   * @param array $data Log in data as an associative array, e.g.
   * username/password.
   * @return boolean True if successfully logged in, false otherwise.
   */
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
    $this->createSession($this->user, $cookie);
    return true;
  }

  /**
   * Log out and delete session. Removes cookies and session variables.
   */
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
