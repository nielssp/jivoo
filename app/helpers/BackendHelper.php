<?php

class BackendHelper extends ApplicationHelper {
  public function requireAuth() {
    $permissions = func_get_args();
    $access = true;
    foreach ($permissions as $permission) {
      if (!$this->auth->hasPermission($permission)) {
        $access = false;
        break;
      }
    }
    if ($access) {
      return;
    }
    if ($this->auth->hasPermission('backend.access')) {
      $this->accessDenied();
    }
    else {
      $this->login();
    }
  }
  
  public function accessDenied() {
    $this->m->Routes->redirect(array(
      'controller' => 'Backend',
      'action' => 'accessDenied'
    ));
  }
  
  public function login() {
    $this->session['returnTo'] = array(
      'path' => $this->request->path,
      'query' => $this->request->query
    );
    $this->m->Routes->redirect(array(
      'controller' => 'Backend',
      'action' => 'login'
    ));
  }
}