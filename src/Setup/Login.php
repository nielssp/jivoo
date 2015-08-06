<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

use Jivoo\Snippets\Snippet;

/**
 * Log in using the maintenance user.
 */
class Login extends Snippet {
  /**
   * {@inheritdoc}
   */
  protected $parameters = array('Auth');

  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Form');

  /**
   * {@inheritdoc}
   */
  public function post($data) {
    if ($this->Auth->logIn())
      return $this->redirect(null);
    else
      $this->session->flash->error = tr('Incorrect username and/or password.');
    return $this->get();
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    $this->viewData['title'] = 'Log in';
    $this->viewData['enableNext'] = true;
    return $this->render();
  }
}