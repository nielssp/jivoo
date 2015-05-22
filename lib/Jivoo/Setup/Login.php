<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Snippets\Snippet;

class Login extends Snippet {
  
  protected $parameters = array('Auth');
  
  protected $helpers = array('Form');
  
  public function post($data) {
    if ($this->Auth->logIn())
      return $this->redirect(null);
    else
      $this->session->flash->error = tr('Incorrect username and/or password.');
    return $this->get();
  }
  
  public function get() {
    $this->viewData['title'] = 'Log in';
    $this->viewData['enableNext'] = true;
    return $this->render();
  }
}