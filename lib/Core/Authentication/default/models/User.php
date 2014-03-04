<?php
class User extends ActiveModel {

  protected $hasMany = array(
    'sessions' => 'Session'
  );

  protected $belongsTo = array(
    'Group',
  );

  protected $validate = array(
    'username' => array(
      'presence' => true,
    ),
    'password' => array(
      'presence' => true,
    ),
    'email' => array(
      'presence' => true,
      'email' => true
    ),
    'confirmPassword' => array(
      'presence' => true,
      'ruleConfirm' => array(
        'callback' => 'confirmPassword',
        'message' => 'The two passwords are not identical'
      ),
    ),
  );

  protected $labels = array(
    'username' => 'Username',
    'email' => 'Email',
    'password' => 'Password',
    'confirmPassword' => 'Confirm password',
  );

  protected $defaults = array(
    'groupId' => 0,
  );

  public function recordHasPermission(ActiveRecord $record, $key) {
    $group = $record->group;
    return isset($group) and $group->hasPermission($key);
  }

  public function confirmPassword($value) {
    return $value == $this->password;
  }
}
