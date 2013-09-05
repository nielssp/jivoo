<?php

class BackendHelper extends Helper {

  protected $modules = array('Theme');
  
  protected $helpers = array('Auth');

  public function setTheme() {
    $this->m->Theme->loadThemeFor('backend');
  }
  
  public function requireAuth() {
    $permissions = func_get_args();
    $access = true;
    foreach ($permissions as $permission) {
      if (!$this->Auth->hasPermission($permission)) {
        $access = false;
        break;
      }
    }
    if ($access) {
      return;
    }
    if ($this->Auth->hasPermission('backend.access')) {
      $this->accessDenied();
    }
    else {
      $this->login();
    }
  }

  public function accessDenied() {
    $this->m->Routing
      ->redirect(array('controller' => 'Backend', 'action' => 'accessDenied'));
  }

  public function login() {
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
    $this->m->Routing
      ->redirect(array('controller' => 'Backend', 'action' => 'login'));
  }
}
