<?php

class BackendController extends ApplicationController {

  protected $helpers = array('Html', 'Form', 'Backend');
  
  public function dashboard() {
    $this->Backend->requireAuth('backend.access');
    $this->title = tr('Dashboard');
    $this->render();
  }
  
  public function about() {
    if ($this->m->Templates->hideIdentity()) {
      $this->Backend->requireAuth('backend.access');
    }
    $this->title = tr('About');
    $this->render();
  }
  
  public function accessDenied() {
    $this->Backend->requireAuth('backend.access');
    $this->title = tr('Access Denied');
    $this->render();
  }

  public function configuration() {
    $this->Backend->requireAuth('backend.configuration');
    $this->title = tr('Configuration');
    $this->render();
  }
  
  public function logout() {
    if (!$this->auth->isLoggedIn()) {
      $this->redirect(null);
    }
    $this->auth->logOut();
    $this->goBack();
    $this->refresh();
  }

  public function login() {
    if ($this->auth->isLoggedIn()) {
      $this->redirect(array('action' => 'dashboard'));
    }
    $this->title = tr('Log in');
    $this->noHeader = true;

    $this->login = new Form('login');
    
    $this->login->addString('username', tr('Username'));
    $this->login->addString('password', tr('Password'));
    
    if ($this->request->isPost()) {
      $this->login->addData($this->request->data['login']);
      if ($this->login->isValid()) {
        if ($this->auth->logIn($this->login->username, $this->login->password)) {
          $this->goBack();
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
