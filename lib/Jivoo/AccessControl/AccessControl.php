<?php
// Module
// Name           : Access control
// Description    : The Jivoo authentication and authorization system
// Author         : apakoh.dk
// Dependencies   : Jivoo/Routing Jivoo/Helpers Jivoo/Models

Lib::import('Jivoo/AccessControl/Hashing');

/**
 * Access control module
 *
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

class UnsupportedHashTypeException extends Exception { }
