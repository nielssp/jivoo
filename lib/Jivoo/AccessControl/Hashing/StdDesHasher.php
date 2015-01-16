<?php
/**
 * A password hasher using standard DES.
 * @package Jivoo\AccessControl\Hashing
 */
class StdDesHasher extends CryptHasher {
  protected $constant = 'CRYPT_STD_DES';
  protected $saltLength = 2;
}