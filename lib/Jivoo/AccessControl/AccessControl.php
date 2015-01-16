<?php
Lib::import('Jivoo/AccessControl/Hashing');
Lib::import('Jivoo/AccessControl/Authentication');
Lib::import('Jivoo/AccessControl/Authorization');
Lib::import('Jivoo/AccessControl/Acl');

/**
 * Access control module for authentication and authorization.
 * @package Jivoo\AccessControl
 */
class AccessControl extends LoadableModule {
  
  protected $modules = array('Routing', 'Helpers', 'Models');
  
  /**
   * @var string[] List of built-in hashing algorithms
   */
  private $builtIn = array(
    'Sha512Hasher', 'Sha256Hasher', 'BlowfishHasher',
    'Md5Hasher', 'ExtDesHasher', 'StdDesHasher'
  );
  
  private $hashers = array();

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
  
  public function addPasswordHasher(IPasswordHasher $hasher) {
    $this->hashers[get_class($hasher)] = $hasher;
  }
  
  public function getPasswordHasher($hasher = 'Default') {
    return $this->hashers[$hasher];
  }
}

/**
 * Thrown when a hash type is unsupported.
 * @package Jivoo\AccessControl
 */
class UnsupportedHashTypeException extends Exception { }
