<?php

class AuthHelper extends Helper {

  protected $modules = array('Authentication', 'Shadow');

  private $loginRoute = array('action' => 'login');
  
  public function setLoginRoute($route = null) {
    $this->loginRoute = $route;
  }
  
  public function isLoggedIn() {
    return $this->m->Authentication->isLoggedIn();
  }
  
  public function hasPermission($permission) {
    return $this->m->Authentication->hasPermission($permission);
  }
  
  public function getUser() {
    return $this->m->Authentication->getUser();
  }
  
  public function logIn($username, $password, $remember = false) {
    return $this->m->Authentication->logIn($username, $password, $remember);
  }
  
  public function logOut() {
    return $this->m->Authentication->logOut();
  }
  
  public function requireAuth() {
    if (!$this->isLoggedIn()) {
      $this->gotoLogin();
    }
  }
  
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

  public function gotoLogin() {
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
    $this->m->Routing->redirect($this->loginRoute);
  }
  
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
