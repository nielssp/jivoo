<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

use Jivoo\AccessControl\IPasswordHasher;
use Jivoo\Core\Utilities;
use Jivoo\AccessControl\UnsupportedHashTypeException;
use Jivoo\AccessControl\Random;
use Jivoo\Core\Binary;

/**
 * A password hasher using the PHP {@see crypt} function.
 */
abstract class CryptHasher implements IPasswordHasher {
  /**
   * @var string Name of PHP constant associated with the hash type.
   */
  protected $constant = '';
  
  /**
   * @var string Salt prefix
   */
  protected $prefix = '';
  
  /**
   * @var int Length of randomly generated salt.
   */
  protected $saltLength = 0;
  
  /**
   * @var string Allowed characters in salt.
   */
  protected $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
  
  /**
   * Construct hasher.
   * @throws UnsupportedHashTypeException If tha hash type is not supported by
   * the current PHP installation.
   */
  public function __construct() {
    if (!defined($this->constant) or constant($this->constant) != 1) {
      throw new UnsupportedHashTypeException(tr(
        'Unsupported password hasher: "%1"', get_class($this)
      ));
    }
  }
  
  /**
   * Generate a random salt.
   * @return string Salt.
   */
  public function genSalt() {
    $bytes = Random::bytes($this->saltLength);
    $b64 = rtrim(base64_encode($bytes), '=');
    $salt = Binary::slice(str_replace('+', '.', $b64), 0, $this->saltLength);
    return $this->prefix . $salt;
  }
  
  /**
   * {@inheritdoc}
   */
  public function hash($password) {
    return crypt($password, $this->genSalt());
  }

  /**
   * {@inheritdoc}
   */
  public function compare($password, $hash) {
    $actual = crypt($password, $hash);
    if (strlen($actual) != strlen($hash))
      return false;
    $res = $hash ^ $actual;
    $ret = 0;
    for ($i = strlen($res) - 1; $i >= 0; $i--)
      $ret |= ord($res[$i]);
    return $ret === 0;
  }
}