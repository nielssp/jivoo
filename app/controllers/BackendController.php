<?php

class BackendController extends ApplicationController {

  protected $helpers = array('Html', 'Form');
  
  public function dashboard() {
    if (!$this->auth->hasPermission('backend.access')) {
      return $this->login();
    }
    $this->title = tr('Dashboard');
    $this->render();
  }
  
  public function about() {
    if (!$this->auth->hasPermission('backend.access') AND $this->m->Templates->hideIdentity()) {
      return $this->login();
    }
    $this->title = tr('About');
    $this->render();
  }

  public function login() {
    $this->title = tr('Log in');
    $this->noHeader = TRUE;

    $this->login = new Form('login');
    
    $this->login->addString('username', tr('Username'));
    $this->login->addString('password', tr('Password'));
    
    if ($this->request->isPost()) {
      $this->login->addData($this->request->data['login']);
      if ($this->login->isValid()) {
        if ($this->auth->logIn($this->login->username, $this->login->password)) {
          $this->refresh();
        }
        else {
          $this->login->addError('username', tr('Wrong username and/or password.'));
        }
      }
    }
    $this->render();
  }
}
