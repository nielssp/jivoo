<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * A password hasher.
 */
interface PasswordHasher {
  /**
   * Hash a password.
   * @param string $password Cleartext password.
   * @return string Hashed password.
   */
  public function hash($password);
  
  /**
   * Compare a cleartext password to a hash string.
   * @param string $password Cleartext password.
   * @param string $hash Hash string.
   * @return bool True if they match, false otherwise.
   */
  public function compare($password, $hash);
}