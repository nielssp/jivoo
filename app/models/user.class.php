<?php

if (!is_a($this, 'Users')) {
  exit('This model should be loaded from the Users module.');
}

class User extends ActiveRecord implements ILinkable {

  protected $hasMany = array(
  	'Post' => array('thisKey' => 'user_id'),
  	'Comment' => array('thisKey' => 'user_id'),
  );

  protected $belongsTo = array(
    'Group' => array('otherKey' => 'group_id'),
  );

  protected $validate = array(
    'username' => array('presence' => true,
                        'minLength' => 1,
                        'maxLength' => 255),
    'password' => array('presence' => true,
                        'minLength' => 1,
                        'maxLength' => 255),
    'email' => array('presence' => true,
                     'minLength' => 1,
                     'maxLength' => 255),
  );

  private static $users;

  public static function setModule(Users $usersModule) {
    self::$users = $usersModule;
  }

  public function getPath() {
    return array('users', $this->username);
  }

  public function getLink() {
    return self::$users->getLink($this);
  }

  public function hasPermission($key) {
    return $this->getGroup()->hasPermission($key);
  }
}

User::setModule($this);