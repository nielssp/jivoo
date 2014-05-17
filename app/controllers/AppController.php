<?php

class AppController extends Controller {
  
  protected $helpers = array('Html', 'Menu', 'Auth', 'Widgets');
  
  protected function init() {
    $this->Auth->action = 'App::Login';
    $this->Auth->addAuthentication(new FormAuthentication($this->User));
    $this->Auth->addAuthorization(new GroupAuthorization());
  }
  
  public function notFound() {
    $this->setStatus(404);
    return $this->render('404.html');
  }
}
