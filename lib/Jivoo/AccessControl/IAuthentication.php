<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Models\IRecord;

/**
 * A method of authentication.
 */
interface IAuthentication {
  /**
   * Attempt to authenticate a user.
   * @param array $data Associative array of authentication data.
   * @param IUserModel $userModel User model to use for authentication.
   * @param IPasswordHasher $hasher Password hasher used for passwords.
   * @return ActiveRecord|null An authenticated user or null if authentication
   * not possible.
   */
  public function authenticate($data, IUserModel $userModel, IPasswordHasher $hasher);
  
  /**
   * Deauthenticate a user.
   * @param IRecord $user User record.
   * @param IUserModel $userModel User model.
   */
  public function deauthenticate(IRecord $user, IUserModel $userModel);
  
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
  public function isStateLess();
}