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
  
  protected $mixins = array('Timestamps');

  protected $labels = array(
    'username' => 'Username',
    'email' => 'Email',
    'password' => 'Password',
    'confirmPassword' => 'Confirm password',
  );
  
  protected $virtual = array(
    'confirmPassword' => array(
      'beforeGet' => '',
      'beforeSave' => '',
      'beforeValidate' => '',
      'afterSet' => '',
    ),
  );

  protected $defaults = array(
  );

  public function recordHasPermission(ActiveRecord $record, $key) {
    $group = $record->group;
    return isset($group) and $group->hasPermission($key);
  }

  public function confirmPassword(ActiveRecord $record, $field) {
    return $record->password == $record->confirmPassword;
  }
}
