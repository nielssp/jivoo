<?php
class Session extends ActiveModel {
  protected $belongsTo = array(
    'user' => 'Users'
  );
  
  public function recordHasExpired(ActiveRecord $session) {
    return $session->validUntil <= time();
  }
}
