<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

/**
 * A password hasher using Blowfish.
 */
class BlowfishHasher extends CryptHasher {
  protected $constant = 'CRYPT_BLOWFISH';
  protected $saltLength = 22;
  
  /**
   * Construct Blowfish password hasher.
   * @param int $cost A number between 4 and 31 that sets the cost of the hash
   * computation. 
   */
  public function __construct($cost = 10) {
    assume($cost >= 4 and $cost <= 31);
    $this->prefix = sprintf('$2a$%02d$', $cost);
    parent::__construct();
  }
}