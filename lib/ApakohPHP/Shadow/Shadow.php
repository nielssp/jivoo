<?php
// Module
// Name           : Shadow
// Version        : 0.2.0
// Description    : The PeanutCMS hashing and security system
// Author         : PeanutCMS
// Dependencies   : Errors

class Shadow extends ModuleBase {

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
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
    $max = strlen($chars) - 1;
    $salt = '';
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
    for ($i = 0; $i < $saltLength; $i++) {
      $salt .= $chars[mt_rand(0, $max)];
    }
    return $prefix . $salt;
  }

  public function hash($string, $hashType = null) {
    return crypt($string, $this->genSalt($hashType));
  }

  public function compare($string, $hash) {
    return crypt($string, $hash) == $hash;
  }

  public function setPassword($name, $password, $hash = false) {
    if ($hash) {
      $password = $this->hash($password);
    }
  }

  public function getPassword($name) {
  }

  public function comparePassword($name, $password, $hash = false) {
    if ($hash) {
      $password = $this->hash($password);
    }
  }

}
