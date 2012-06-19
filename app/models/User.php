<?php
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

  public function getLink() {
    return array(
      'controller' => 'Users',
      'action' => 'view',
      'parameters' => array($this->username)
    );
  }

  public function hasPermission($key) {
    return $this->getGroup()->hasPermission($key);
  }
}
