<?php
/**
 * Helper class for authentication
 * @package Core\Authentication
 */
class AuthHelper extends Helper {

  protected $helpers = array('Json');
  protected $modules = array('Authentication', 'Shadow');

  /**
   * @var array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  private $loginRoute = 'App::login';
  
  /**
   * Set route to login page
   * @param array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function setLoginRoute($route = null) {
    $this->loginRoute = $route;
  }
  
  /**
   * Check if anyone is logged in
   * @return bool True if logged in, false otherwise
   */
  public function isLoggedIn() {
    return $this->m->Authentication->isLoggedIn();
  }
  
  /**
   * Check current user or guest for a permission
   * @param string $permission Permission key
   * @return boolean True if user has permission, false otherwise
   */
  public function hasPermission($permission) {
    return $this->m->Authentication->hasPermission($permission);
  }
  
  /**
   * Get the current user
   * @return User|false The current user or false if not logged in
   */
  public function getUser() {
    return $this->m->Authentication->getUser();
  }

  /**
   * Log in to user
   * @param string $username Username
   * @param string $password Password
   * @param bool $remember Whether or not to remember log in (set cookie)
   * @return bool True if successful, false otherwise
   */
  public function logIn($username, $password, $remember = false) {
    return $this->m->Authentication->logIn($username, $password, $remember);
  }

  /**
   * Log out of current user and unset sessions and cookies
   */
  public function logOut() {
    return $this->m->Authentication->logOut();
  }
  
  /**
   * Require authentication, redirect to login route if not logged in
   */
  public function requireAuth() {
    if (!$this->isLoggedIn()) {
      $this->gotoLogin();
    }
  }
  
  /**
   * Require permissions, redirect to login route if not authorized
   * @param string $permission,... Permission keys
   */
  public function requirePermissions() {
    $permissions = func_get_args();
    $access = true;
    foreach ($permissions as $permission) {
      if (!$this->m->Authentication->hasPermission($permission)) {
        $access = false;
        break;
      }
    }
    if ($access) {
      return;
    }
    $this->gotoLogin();
  }

  /**
   * Redirect to login route
   */
  public function gotoLogin() {
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
    if ($this->request->isAjax()) {
      $this->Json->respond(array(
        'status' => 'unauthorized',
        'link' => $this->m->Routing->getLink($this->loginRoute)
      ));
      exit;
    }
    $this->m->Routing->redirect($this->loginRoute);
  }
  
  /**
   * Save a User record
   * @param User $user User
   * @param Group $group Group to put user in
   * @return boolean True if successful, false if invalid
   */
  public function save(User $user, Group $group = null) {
    if (!$user->isValid()) {
      return false;
    }
    $user->password = $this->m->Shadow->hash($user->password);
    if (!isset($group)) {
      $group = $this->m->Authentication->getDefaultGroup();
    }
    if ($group) {
      $user->setGroup($group);
    }
    return $user->save(array('validate' => false));
  }
}
