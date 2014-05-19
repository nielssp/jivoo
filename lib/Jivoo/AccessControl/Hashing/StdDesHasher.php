<?php
class StdDesHasher extends CryptHasher {
  protected $constant = 'CRYPT_STD_DES';
  protected $saltLength = 2;
}