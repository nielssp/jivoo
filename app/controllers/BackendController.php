<?php

class BackendController extends ApplicationController {

  protected $helpers = array('Html', 'Form');
  
  public function dashboard() {
    if (!$this->m->Authentication->isLoggedIn()) {
      $this->login();
      return;
    }
    $this->title = tr('Dashboard');
    $this->render();
  }
  
  public function about() {
    if (!$this->m->Authentication->isLoggedIn() AND $this->m->Templates->hideIdentity()) {
      $this->login();
      return;
    }
    $this->title = tr('About');
    $this->render();
  }

  public function login() {
    $this->title = tr('Log in');
    $this->noHeader = TRUE;

    if ($this->request->isPost()) {
      if ($this->m->Authentication->logIn($this->request->data['login_username'], $this->request->data['login_password'])) {
        $this->refresh();
      }
      else {
        $this->loginError = TRUE;
        $this->loginUsername = h($this->request->data['login_username']);
      }
    }
    $this->render();
  }
}
