<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

/**
 * A password hasher using MD5.
 */
class Md5Hasher extends CryptHasher {
  protected $constant = 'CRYPT_MD5';
  protected $saltLength = 8;
  protected $prefix = '$1$';
}