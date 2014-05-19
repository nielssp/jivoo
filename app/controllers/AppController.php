<?php

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Menu', 'Auth', 'Widgets');
  
  protected $models = array('User');
  
  protected function init() {
  }
  
  public function notFound() {
    $this->setStatus(404);
    return $this->render();
  }
  
  public function login() {
    if ($this->request->isPost()) {
      if ($this->Auth->logIn()) {
        return $this->Auth->redirect();
      }
      else {
        $this->session->alert(tr('Incorret username and/or password.'));
      }
    }
    return $this->render();
  }
}
