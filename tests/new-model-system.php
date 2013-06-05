<?php
include '../lib/Core/bootstrap.php';

Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');

interface IModel {
  public function create($data = array());
  public function all(SelectQuery $query = null);
  public function first(SelectQuery $query = null);
  public function last(SelectQuery $query = null);
  public function count(SelectQuery $query = null);
}

interface IRecord {
  public function __get($property);
  public function __set($property, $value);
  public function getModel();
  public function save();
  public function delete();
  public function isNew();
  public function isSaved();
}

abstract class ActiveModel implements IModel {
  public final function __construct(IDataSource $dataSource, AppConfig $config) {
    
  }
}

// class ActiveRecord implements IRecord {
// }

$db = new PdoMysqlDatabase(array(
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

class User extends ActiveRecord {
  public static $hasMany = array(
    'Post' => array('thisKey' => 'user_id'),
    'Comment' => array('thisKey' => 'user_id'),
  );

  public static $belongsTo = array(
    'Group' => array('otherKey' => 'group_id'),
  );

  public static $validate = array(
    'username' => array('presence' => true,),
    'password' => array('presence' => true,),
    'email' => array('presence' => true, 'email' => true),
    'confirm_password' => array(
      'presence' => true,
      'ruleConfirm' => array(
        'callback' => 'confirmPassword',
        'message' => 'The two passwords are not identical'
      ),
    ),
  );

  public static $labels = array(
    'username' => 'Username',
    'email' => 'Email',
    'password' => 'Password',
    'confirm_password' => 'Confirm password',
  );

  public static $defaults = array(
    'cookie' => '',
    'session' => '',
    'ip' => '',
    'group_id' => 0,
  );
}