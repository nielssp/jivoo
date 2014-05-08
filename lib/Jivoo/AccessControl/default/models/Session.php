<?php
class Session extends ActiveModel {
  protected $belongsTo = array(
    'user' => 'User'
  );
  
  public function recordHasExpired(ActiveRecord $session) {
    return $session->validUntil <= time();
  }
}
