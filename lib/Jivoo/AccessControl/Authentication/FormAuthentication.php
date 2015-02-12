<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Authentication;

use Jivoo\AccessControl\LoadableAuthentication;
use Jivoo\AccessControl\IUserModel;
use Jivoo\AccessControl\IPasswordHasher;

/**
 * Authentication using a POST-method form. Expects fields named "username" and
 * "password". If a field "remember" is set, a long-lived cookie will be
 * created. The names of the fields can be changed with options "username"
 * and "password".
 * @package Jivoo\AccessControl\Authentication
 */
class FormAuthentication extends LoadableAuthentication {
  /**
   * @var bool Create cookie.
   */
  private $cookie = false;
  
  /**
   * {@inheritdoc}
   */
  protected $options = array(
    'username' => 'username',
    'password' => 'password'
  );

  /**
   * {@inheritdoc}
   */
  public function authenticate($data, IUserModel $userModel, IPasswordHasher $hasher) {
    $this->cookie = isset($data['remember']);
    $user = $userModel->where($this->options['username'] . ' = %s', $data['username'])
      ->first();
    if ($user) {
      $passwordField = $this->options['password'];
      if ($hasher->compare($data['password'], $user->$passwordField))
        return $user;
    }
    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function cookie() {
    return $this->cookie;
  }
}
