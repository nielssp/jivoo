<?php
class BlowfishHasher extends CryptHasher {
  protected $constant = 'CRYPT_BLOWFISH';
  protected $saltLength = 22;
  public function __construct($cost = 10) {
    assume($cost >= 4 and $cost <= 31);
    $this->prefix = sprintf('$2a$%02d$', $cost);
    parent::__construct();
  }
}