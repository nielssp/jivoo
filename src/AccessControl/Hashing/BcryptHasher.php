<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

use Jivoo\AccessControl\UnsupportedHashTypeException;

/**
 * A password hasher using Blowfish (with PHP 5.3.7 security fixes).
 */
class BcryptHasher extends CryptHasher {
  protected $constant = 'CRYPT_BLOWFISH';
  protected $saltLength = 22;
  
  /**
   * Construct Blowfish password hasher.
   * @param int $cost A number between 4 and 31 that sets the cost of the hash
   * computation. 
   */
  public function __construct($cost = 10) {
    assume($cost >= 4 and $cost <= 31);
    $this->prefix = sprintf('$2y$%02d$', $cost);
    if (PHP_VERSION_ID < 50307) {
      throw new UnsupportedHashTypeException(tr(
        'Unsupported password hasher: "%1"', get_class($this)
      ));
    }
    parent::__construct();
  }
}