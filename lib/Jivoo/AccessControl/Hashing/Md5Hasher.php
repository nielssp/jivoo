<?php
class Md5Hasher extends CryptHasher {
  protected $constant = 'CRYPT_MD5';
  protected $saltLength = 8;
  protected $prefix = '$1$';
}