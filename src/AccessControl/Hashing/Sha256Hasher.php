<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

/**
 * A password hasher using SHA-256.
 */
class Sha256Hasher extends CryptHasher {
  protected $constant = 'CRYPT_SHA256';
  protected $saltLength = 16;

  /**
   * Construct SHA-256 password hasher.
   * @param int $rounds Number of rounds for SHA-512 algorithm.
   */
  public function __construct($rounds = 5000) {
    assume($rounds >= 1000 and $rounds <= 999999999);
    $this->prefix = '$5$rounds=' . $rounds . '$';
    parent::__construct();
  }
}