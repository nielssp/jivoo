<?php
class Sha256Hasher extends CryptHasher {
  protected $constant = 'CRYPT_SHA256';
  protected $saltLength = 16;
  public function __construct($rounds = 5000) {
    assume($rounds >= 1000 and $rounds <= 999999999);
    $this->prefix = '$5$rounds=' . $rounds . '$';
    parent::__construct();
  }
}