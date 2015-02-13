<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl\Hashing;

use Jivoo\AccessControl\IPasswordHasher;
use Jivoo\Core\Utilities;

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
    return $this->prefix . Utilities::randomString($this->saltLength, $this->allowedChars);
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
    return crypt($password, $hash) === $hash;
  }
}