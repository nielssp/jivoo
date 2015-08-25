<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Helpers\Helper;
use Jivoo\AccessControl\Acl\DefaultAcl;
use Jivoo\Core\Utilities;
use Jivoo\Routing\RenderEvent;

/**
 * Helper class for authentication and autorization.
 * @property UserModel $userModel User model.
 * @property array|Linkable|string|null $loginRoute Route for login page, ee
 * {@see Routing}.
 * @property array|Linkable|string|null $unauthorizedRoute Route to redirect to
 * when user unauthorized, see {@see Routing}.
 * @property array|Linkable|string|null $ajaxRoute Route to redirect to for
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
 * @property PasswordHasher $passwordHasher Password hasher used to hash
 * passwords.
 * @property string $permissionPrefix Prefix for permissions when checking
 * access control lists.
 * @property-read mixed $user User data of current user if logged in, otherwise
 * null.
 * @property-write string|Authentication|string[]|Authentication[]|array[] $authentication
 * Add one or more access control lists. Can be the name of a class (without
 * 'Authentication'-suffix) or a list of names, see
 * {@see LoadableAuthentication}. Can also be an associative array mapping names
 * to options.
 * @property-write string|Authorization|string[]|Authorization[]|array[] $authorization
 * Add one or more access control lists. Can be the name of a class (without
 * 'Authorization'-suffix) or a list of names, see {@see LoadableAuthorization}.
 * Can also be an associative array mapping names to options.
 * @property-write string|Acl|string[]|Acl[]|array[] $acl
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
   * @var mixed Current user, if logged in. 
   */
  private $user = null;
  
  /**
   * @var string Current session id.
   */
  private $sessionId = null;

  /**
   * @var UserModel User model.
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
   * @param array|Linkable|string|null $route Route for login page.
   */
  private $loginRoute = null;
  
  /**
   * @param array|Linkable|string|null $route Route unauthorized page.
   */
  private $unauthorizedRoute = null;
  
  /**
   * @var array|Linkable|string|null $route Route for AJAX requests.
   */
  private $ajaxRoute = null;
  
  /**
   * @var Authentication[] Associative array of authentication methods.
   */
  private $authenticationMethods = array();
  
  /**
   * @var Authentication[] Associative array of stateless authentication methods.
   */
  private $statelessAuthenticationMethods = array();

  /**
   * @var Authorization[] Associative array of authorization methods.
   */
  private $authorizationMethods= array();
  
  /**
   * @var Acl[] Associative array of ACL handlers.
   */
  private $aclMethods = array();
  
  /**
   * @var (Authentication|Authorization|Acl)[]
   */
  private $acModules = array();
  
  /**
   * @var PasswordHasher Password hasher.
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
    $this->m->Routing->on('beforeDispatch', array($this, 'checkAuthorization'));
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
    if (isset($this->acModules[$property]))
      return $this->acModules[$property];
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
        if ($value instanceof PasswordHasher)
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
    parent::__set($property, $value);
  }
  
  /**
   * Load authentication module.
   * @param string|Authentication $name Name or object.
   * @param array $options Options for new object.
   */
  private function loadAuthentication($name, $options = array()) {
    if ($name instanceof Authentication)
      return $this->addAuthentication($name);
    $class = 'Jivoo\AccessControl\Authentication\\' . $name . 'Authentication';
    Utilities::assumeSubclassOf($class, 'Jivoo\AccessControl\LoadableAuthentication');
    $this->addAuthentication(new $class($this->app, $options), $name);
  }
  
  /**
   * Load authorization module.
   * @param string|Authorization $name Name or object.
   * @param array $options Options for new object.
   */
  private function loadAuthorization($name, $options = array()) {
    if ($name instanceof Authorization)
      return $this->addAuthorization($name);
    $class = 'Jivoo\AccessControl\Authorization\\' . $name . 'Authorization';
    Utilities::assumeSubclassOf($class, 'Jivoo\AccessControl\LoadableAuthorization');
    $this->addAuthorization(new $class($this->app, $options, $this), $name);
  }

  /**
   * Load ACL module.
   * @param string|Acl $name Name or object.
   * @param array $options Options for new object.
   */
  private function loadAcl($name, $options = array()) {
    if ($name instanceof Acl)
      return $this->addAcl($name);
    $class = 'Jivoo\AccessControl\Acl\\' . $name . 'Acl';
    Utilities::assumeSubclassOf($class, 'Jivoo\AccessControl\LoadableAcl');
    $this->addAcl(new $class($this->app, $options), $name);
  }
  
  /**
   * Add an authentication module.
   * @param Authentication $authentication Module.
   * @param string $name Name that can be used to later access the module using
   * {@see __get}, default is the class name (without namespace).
   */
  public function addAuthentication(Authentication $authentication, $name = null) {
    $this->authenticationMethods[] = $authentication;
    if ($authentication->isStateless())
      $this->statelessAuthenticationMethods[] = $authentication;
    if (!isset($name))
      $name = Utilities::getClassName($authentication);
    $this->acModules[$name] = $authentication;
  }
  
  /**
   * Add an authorization module.
   * @param Authorization $authorization Module.
   * @param string $name Name that can be used to later access the module using
   * {@see __get}, default is the class name (without namespace).
   */
  public function addAuthorization(Authorization $authorization, $name = null) {
    $this->authorizationMethods[] = $authorization;
    if (!isset($name))
      $name = Utilities::getClassName($authorization);
    $this->acModules[$name] = $authorization;
  }
  
  /**
   * Add an ACL module.
   * @param Acl $acl Module.
   * @param string $name Name that can be used to later access the module using
   * {@see __get}, default is the class name (without namespace).
   */
  public function addAcl(Acl $acl, $name = null) {
    $this->aclMethods[] = $acl;
    if (!isset($name))
      $name = Utilities::getClassName($acl);
    $this->acModules[$name] = $acl;
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
    return isset($this->userModel)
      and (isset($this->user)
      or $this->checkSession()
      or $this->checkCookie()
      or $this->checkStateless());
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
      if ($method->hasPermission($permission, $this->user))
        return true;
    }
    if (strpos($permission, '.') !== false) {
      if ($this->checkAcl(preg_replace('/\\.[^\.]+?$/', '', $permission)))
        return true;
    }
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
   * Check user authorization for the current route. 
   * @param RenderEvent $event The action event.
   */
  public function checkAuthorization(RenderEvent $event) {
    if (!$this->hasAuthorization($event->route))
      $this->authorizationError();
  }
  
  /**
   * Check user authorization for a route.
   * @param array|\Jivoo\Routing\Linkable|string|null $route A route,
   * see {@see \Jivoo\Routing\Routing}.
   * @param mixed $user Optional user data, if null the current user is used.
   * @return bool True if user is authorized.
   */
  public function hasAuthorization($route, $user = null) {
    if (count($this->authorizationMethods) == 0)
      return true;
    if (!isset($user))
      $user = $this->getUser();
    $route = $this->m->Routing->validateRoute($route);
    if (isset($route['void'])) {
      return true;
    }
    $authRequest = new AuthorizationRequest($route, $user);
    foreach ($this->authorizationMethods as $method) {
      if ($method->authorize($authRequest))
        return true;
    }
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
  public function checkStateless() {
    foreach ($this->statelessAuthenticationMethods as $method) {
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
   * @return mixed User data of current user, null if not logged in.
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
      $sessionId, time() + $this->cookieLifeTime,
      null, null, $this->request->secure, true
    );
    if ($this->cookieRenewAfter >= 0) {
      $this->request->cookies->setCookie(
        $this->cookiePrefix . 'renew_at',
        time() + $this->cookieRenewAfter, time() + $this->cookieLifeTime,
        null, null, $this->request->secure, true
      );
    }
  }

  /**
   * Create a session for user.
   * @param mixed $user User data to create session for.
   * @param string $cookie Whether or not to make the session long-lived, i.e.
   * remember the user for the next visit.
   */
  public function createSession($user, $cookie = false) {
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
