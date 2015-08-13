<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Utilities;

/**
 * Access control module for authentication and authorization.
 */
class AccessControl extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing', 'Helpers');
  
  /**
   * @var string[] List of built-in hashing algorithms.
   */
  private $builtIn = array(
    'Jivoo\AccessControl\Hashing\BcryptHasher',
    'Jivoo\AccessControl\Hashing\Sha512Hasher',
    'Jivoo\AccessControl\Hashing\Sha256Hasher',
    'Jivoo\AccessControl\Hashing\BlowfishHasher',
    'Jivoo\AccessControl\Hashing\Md5Hasher',
    'Jivoo\AccessControl\Hashing\ExtDesHasher',
    'Jivoo\AccessControl\Hashing\StdDesHasher'
  );
  
  /**
   * @var PasswordHasher[] Associative array of named password hasshers.
   */
  private $hashers = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $defaultHasher = $this->config->get('defaultHasher');
    foreach ($this->builtIn as $builtIn) {
      try {
        $passwordHasher = new $builtIn();
        $this->hashers[$builtIn] = $passwordHasher;
        if (!isset($defaultHasher) or !isset($this->hashers[$defaultHasher])) {
          $defaultHasher = $builtIn;
          $this->config['defaultHasher'] = $builtIn;
        }
      }
      catch (UnsupportedHashTypeException $e) { }
    }
    $this->hashers['Default'] = $this->hashers[$defaultHasher];
    
    $this->m->Helpers->addHelper('Jivoo\AccessControl\AuthHelper');
  }
  
  /**
   * Add a password hasher.
   * @param PasswordHasher $hasher Password hasher.
   */
  public function addPasswordHasher(PasswordHasher $hasher) {
    $this->hashers[Utilities::getClassName($hasher)] = $hasher;
  }
  
  /**
   * Get a named password hasher.
   * @param string $hasher Name of password hasher, or 'Default' for default
   * password hasher.
   * @return PasswordHasher Password hasher.
   */
  public function getPasswordHasher($hasher = 'Default') {
    return $this->hashers[$hasher];
  }
}
