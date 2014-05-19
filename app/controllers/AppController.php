<?php

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Menu', 'Auth', 'Widgets', 'Form');
  
  protected $models = array('User');
  
  protected function init() {
    $this->Auth->userModel = $this->User;
    $this->Auth->authentication = array('Form');
    if ($this->Auth->isLoggedIn())
      $this->user = $this->Auth->user;
  }
  
  public function notFound() {
    $this->setStatus(404);
    return $this->render();
  }
  
  public function login() {
    if ($this->request->isPost()) {
      if ($this->Auth->logIn()) {
        $this->session->alert(tr('Logged in.. maybe?'));
      }
      else {
        $this->session->alert(tr('Incorret username and/or password.'));
      }
    }
    return $this->render();
  }
}
