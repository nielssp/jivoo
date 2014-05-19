<?php
abstract class CryptHasher implements IPasswordHasher {
  
  protected $constant = '';
  protected $prefix = '';
  protected $saltLength = 0;
  protected $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./';
  
  public function __construct() {
    if (!defined($this->constant) or constant($this->constant) != 1) {
      throw new UnsupportedHashTypeException(tr(
        'Unsupported password hasher: "%1"', get_class($this)
      ));
    }
  }
  
  public function genSalt() {
    return $this->prefix . Utilities::randomString($this->saltLength, $this->allowedChars);
  }
  
  public function hash($password) {
    return crypt($password, $this->genSalt());
  }
  
  public function compare($password, $hash) {
    return crypt($password, $hash) === $hash;
  }
}