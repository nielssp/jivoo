<?php
// Module
// Name           : Shadow
// Description    : The Apakoh Core hashing and security system
// Author         : apakoh.dk

/**
 * Hashing and security module
 * 
 * Supported hash types: 'sha512', 'sha256',
 * 'blowfish', 'md5', 'ext_des' and 'std_des'
 * @package Core\Shadow
 */
class Shadow extends ModuleBase {

  /**
   * @var string[] List of supported hash types
   */
  private $hashTypes = array('sha512', 'sha256', 'blowfish', 'md5', 'ext_des',
    'std_des'
  );

  protected function init() {
    if (!isset($this->config['hashType'])) {
      foreach ($this->hashTypes as $hashType) {
        $constant = 'CRYPT_' . strtoupper($hashType);
        if (defined($constant) AND constant($constant) == 1) {
          $this->config['hashType'] = $hashType;
          break;
        }
      }
    }
  }
  
  /**
   * Generate a random UID
   * @param int $length Length of UID
   * @return string UID
   */
  public function genUid($length = 32) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
    $max = strlen($chars) - 1;
    $uid = '';
    for ($i = 0; $i < $length; $i++) {
      $uid .= $chars[mt_rand(0, $max)];
    }
    return $uid;
  }

  /**
   * Generate a random salt for a specific hash
   * @uses mt_rand() for random numbers
   * @param string $hashType Hash type, if not set the configuration will be
   * used to determine hash type.
   * @return string Random salt
   */
  public function genSalt($hashType = null) {
    if (!isset($hashType)) {
      $hashType = $this->config['hashType'];
      if ($hashType == 'auto') {
        foreach ($this->hashTypes as $t) {
          $constant = 'CRYPT_' . strtoupper($t);
          if (defined($constant) AND constant($constant) == 1) {
            $hashType = $t;
          }
        }
      }
    }
    switch (strtolower($hashType)) {
      case 'sha512':
        $saltLength = 16;
        // rounds from 1000 to 999,999,999
        $prefix = '$6$rounds=5001$';
        break;
      case 'sha256':
        $saltLength = 16;
        // rounds from 1000 to 999,999,999
        $prefix = '$5$rounds=5001$';
        break;
      case 'blowfish':
        $saltLength = 22;
        // cost (second param) from 04 to 31
        $prefix = '$2a$09$';
        break;
      case 'md5':
        $saltLength = 8;
        $prefix = '$1$';
        break;
      case 'ext_des':
        $saltLength = 4;
        // iterations (4 characters after _) from .... to zzzz
        $prefix = '_J9..';
        break;
      case 'std_des':
      default:
        $saltLength = 2;
        $prefix = '';
        break;
    }
    return $prefix . $this->genUid($saltLength);
  }

  /**
   * Hash a string
   * @uses crypt() to hash string
   * @param string $string String to hash
   * @param string $hashType Hash type, if not set the configuration will be
   * used to determine hash type.
   * @return string Hashed string 
   */
  public function hash($string, $hashType = null) {
    return crypt($string, $this->genSalt($hashType));
  }

  /**
   * Compare an unhashed string with a hashed string
   * @uses crypt() to hash string
   * @param string $string Unhashed string
   * @param string $hash Hashed string
   * @return boolean True if the two strings are equal, false otherwise
   */
  public function compare($string, $hash) {
    return crypt($string, $hash) == $hash;
  }

  /**
   * Save a password in shadow-file
   * @TODO implementation  
   * @param string $name Name of password
   * @param string $password Password to save
   * @param bool $hash Whether or not hash the password, default is to not hash.
   * If hashed, the original password can't be retrieved again.
   */
  public function setPassword($name, $password, $hash = false) {
    if ($hash) {
      $password = $this->hash($password);
    }
  }

  /**
   * Get a password or password hash from shadow-file
   * @TODO implementation  
   * @param string $name Name of password
   */
  public function getPassword($name) {
  }

  /**
   * Compare a password with a password in the shadow-file
   * @TODO implementation  
   * @param string $name Name of password
   * @param string $password Unhashed password
   * @param string $hash Whether or not to hash password before comparing
   */
  public function comparePassword($name, $password, $hash = false) {
    if ($hash) {
      $password = $this->hash($password);
    }
  }

}
