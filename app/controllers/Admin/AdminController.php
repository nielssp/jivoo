<?php

class AdminController extends AppController {
  
  protected $helpers = array('Html', 'Form', 'Admin');

  public function before() {
  }
  
  public function index() {
    if ($this->Auth->isLoggedIn()) {
      $this->redirect('dashboard');
    }
    $this->redirect('login');
  }
  
  public function dashboard() {
    $this->Backend->requireAuth('backend.access');
    $this->title = tr('Dashboard');
    $this->render();
  }

  public function about() {
    $this->Backend->requireAuth('backend.access');
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
    $this->configuration = new ConfigurationForm();
    $this->configuration->add(new ConfigurationSection(tr('Site')));
    $this->render();
  }

  public function logout() {
    if (!$this->Auth->isLoggedIn()) {
      $this->redirect(null);
    }
    $this->Auth->logOut();
    $this->goBack();
    $this->refresh();
  }

  public function login() {
    if ($this->Auth->isLoggedIn()) {
      $this->redirect(array('action' => 'dashboard'));
    }
    $this->title = tr('Log in');
    $this->noHeader = true;

    $this->login = new Form('login');

    $this->login->getModel()->addString('username', tr('Username'));
    $this->login->getModel()->addString('password', tr('Password'));

    if ($this->request->isPost()) {
      $this->login->addData($this->request->data['login']);
      if ($this->login->isValid()) {
        if ($this->Auth->logIn($this->login->username, $this->login->password)) {
          $this->goBack();
          $this->refresh();
        }
        else {
          $this->login
            ->addError('username', tr('Wrong username and/or password.'));
        }
      }
    }
    $this->render();
  }
}
