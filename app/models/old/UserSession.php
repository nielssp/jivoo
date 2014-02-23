<?php
class UserSession extends ActiveRecord {
  protected $belongsTo = array(
    'User'
  );
  
  public function hasExpired() {
    return $this->valid_until <= time();
  }
}