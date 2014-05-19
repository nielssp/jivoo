<?php
class ExtDesHasher extends CryptHasher {
  protected $constant = 'CRYPT_EXT_DES';
  protected $saltLength = 4;
  public function __construct($iterations = 751) {
    assume($iterations > 0 and $iterations <= 15752960);
    $bytes = array(
      $iterations & 0x3F,
      ($iterations >> 6) & 0x3F,
      ($iterations >> 12) & 0x3F,
      ($iterations >> 18) & 0x3F
    );
    $base64Chars = './0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $iterations = '';
    foreach ($bytes as $byte) {
      $iterations .= $base64Chars[$byte];
    }
    $this->prefix = '_' . $iterations;
    parent::__construct();
  }
}