<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Lib;

/**
 * Access control module for authentication and authorization.
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
    'Jivoo\AccessControl\Hashing\Sha512Hasher',
    'Jivoo\AccessControl\Hashing\Sha256Hasher',
    'Jivoo\AccessControl\Hashing\BlowfishHasher',
    'Jivoo\AccessControl\Hashing\Md5Hasher',
    'Jivoo\AccessControl\Hashing\ExtDesHasher',
    'Jivoo\AccessControl\Hashing\StdDesHasher'
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
        $this->hashers[Lib::getClassName($builtIn)] = $passwordHasher;
        if (!isset($this->config['defaultHasher']))
          $this->config['defaultHasher'] = $builtIn;
      }
      catch (UnsupportedHashTypeException $e) { }
    }
    $this->hashers['Default'] = $this->hashers[$this->config['defaultHasher']];
    
    $this->m->Helpers->addHelper('Jivoo\AccessControl\AuthHelper');
  }
  
  /**
   * Add a password hasher.
   * @param IPasswordHasher $hasher Password hasher.
   */
  public function addPasswordHasher(IPasswordHasher $hasher) {
    $this->hashers[Lib::getClassName($hasher)] = $hasher;
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
 */
class UnsupportedHashTypeException extends \Exception { }
