<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * A method of authentication.
 */
interface Authentication {
  /**
   * Attempt to authenticate a user.
   * @param array $data Associative array of authentication data.
   * @param UserModel $userModel User model to use for authentication.
   * @param PasswordHasher $hasher Password hasher used for passwords.
   * @return mixed|null User data (e.g. an {@see Jivoo\Models\BasicRecord})
   * or null on failure.
   */
  public function authenticate($data, UserModel $userModel, PasswordHasher $hasher);
  
  /**
   * Deauthenticate a user.
   * @param mixed $userData User data.
   * @param UserModel $userModel User model.
   */
  public function deauthenticate($userData, UserModel $userModel);
  
  /**
   * Whether or not a cookie (for long-lived sessions) should be created based
   * on the most recent call to {@see authenticate}.
   * @return bool True if a cookie should be created, false otherwise.
   */
  public function cookie();
  
  /**
   * Whether or not this method of authentication is stateless.
   * @return bool True if stateless, false otherwise.
   */
  public function isStateless();
}