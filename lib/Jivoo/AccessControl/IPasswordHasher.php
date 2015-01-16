<?php
/**
 * A password hasher.
 * @package Jivoo\AccessControl
 */
interface IPasswordHasher {
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