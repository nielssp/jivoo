<?php
namespace Blog\Controllers;

use Jivoo\Controllers\Controller;
use Jivoo\AccessControl\SingleUserModel;

class AppController extends Controller {
  
  protected $helpers = array('Auth', 'Form');
  
  public function before() {
    $this->config->defaults = array(
      'username' => 'admin',
      'password' => $this->Auth->passwordHasher->hash('admin')
    );
    $this->Auth->userModel = new SingleUserModel(
      $this->session, $this->config['username'], $this->config['password']
    );
    $this->Auth->authentication = 'Form';
    $this->Auth->loginRoute = 'action:App::login';
  }

  public function login() {
    if ($this->Auth->isLoggedIn())
      $this->redirect(null);
    $this->title = tr('Log in');
  
    if ($this->request->hasValidData()) {
      if ($this->Auth->logIn()) {
        $this->goBack();
        return $this->refresh();
      }
      else {
        $this->session->flash->alert = tr('Incorrect username and/or password.');
      }
    }
    return $this->render();
  }
  
  public function logout() {
    if (!$this->Auth->isLoggedIn()) {
      $this->redirect(null);
    }
    $this->Auth->logOut();
    $this->redirect();
  }
  
  public function notFound() {
    $this->title = tr('Not found');
    $this->setStatus(404);
    return $this->render();
  }
}