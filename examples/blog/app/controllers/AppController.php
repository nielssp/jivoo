<?php
namespace Blog\Controllers;

use Jivoo\Controllers\Controller;
use Jivoo\AccessControl\SingleUserModel;
use Jivoo\Models\Form;

class AppController extends Controller {
  
  protected $helpers = array('Auth', 'Form');
  
  public function before() {
    $this->config->defaults = array(
      'title' => 'My Blog',
      'username' => 'admin',
      'password' => $this->Auth->passwordHasher->hash('admin')
    );
    $this->Auth->userModel = new SingleUserModel(
      $this->session, $this->config['username'], $this->config['password']
    );
    $this->Auth->authentication = 'Form';
    $this->Auth->loginRoute = 'action:App::login';
    
    $this->blogTitle = $this->config['title'];
  }
  
  public function settings() {
    if (!$this->Auth->isLoggedIn())
      $this->Auth->authenticationError();
    $this->title = tr('Settings');

    $form = new Form('Settings');
    $form->addString('title', tr('Title'));
    $form->addString('username', tr('Username'));
    $form->addString('password', tr('Password'));
    $form->addString('confirmPassword', tr('Confirm password'));
    $form->title = $this->config['title'];
    $form->username = $this->config['username'];
    if ($this->request->hasValidData('Settings')) {
      $form->addData($this->request->data['Settings']);
      $this->config['title'] = $form->title;
      $this->config['username'] = $form->username; 
      if (!empty($form->password)) {
        if ($form->password === $form->confirmPassword) {
          $this->config['password'] = $this->Auth->passwordHasher->hash($form->password);
          $this->session->flash->success = tr('Password changed.');
          return $this->refresh();
        }
        else {
          $form->addError('password', tr('The two passwords are not identical.'));
        }
      }
      else {
        $this->session->flash->success = tr('Settings saved.');
        return $this->refresh();
      }
    }
    $this->settings = $form;
    return $this->render();
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
        $this->session->flash->error = tr('Incorrect username and/or password.');
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