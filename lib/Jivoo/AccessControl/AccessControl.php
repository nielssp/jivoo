<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * Access control module for authentication and authorization.
 * @package Jivoo\AccessControl
 */
class AccessControl extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing', 'Helpers', 'Models');
  
  /**
   * @var string[] List of built-in hashing algorithms.
   */
  private $builtIn = array(
    'Sha512Hasher', 'Sha256Hasher', 'BlowfishHasher',
    'Md5Hasher', 'ExtDesHasher', 'StdDesHasher'
  );
  
  /**
   * @var IPasswordHasher[] Associative array of named password hasshers.
   */
  private $hashers = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    foreach ($this->builtIn as $builtIn) {
      try {
        $passwordHasher = new $builtIn();
        $this->hashers[$builtIn] = $passwordHasher;
        if (!isset($this->config['defaultHasher']))
          $this->config['defaultHasher'] = $builtIn;
      }
      catch (UnsupportedHashTypeException $e) { }
    }
    $this->hashers['Default'] = $this->hashers[$this->config['defaultHasher']];
  }
  
  /**
   * Add a password hasher.
   * @param IPasswordHasher $hasher Password hasher.
   */
  public function addPasswordHasher(IPasswordHasher $hasher) {
    $this->hashers[get_class($hasher)] = $hasher;
  }
  
  /**
   * Get a named password hasher.
   * @param string $hasher Name of password hasher, or 'Default' for default
   * password hasher.
   * @return IPasswordHasher Password hasher.
   */
  public function getPasswordHasher($hasher = 'Default') {
    return $this->hashers[$hasher];
  }
}

/**
 * Thrown when a hash type is unsupported.
 * @package Jivoo\AccessControl
 */
class UnsupportedHashTypeException extends \Exception { }
