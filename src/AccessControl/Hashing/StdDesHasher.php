<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

/**
 * A password hasher using standard DES.
 */
class StdDesHasher extends CryptHasher {
  protected $constant = 'CRYPT_STD_DES';
  protected $saltLength = 2;
}