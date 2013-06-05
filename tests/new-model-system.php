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
  public function addData($data);
  public function getModel();
  public function save();
  public function delete();
  public function isNew();
  public function isSaved();
}

class ActiveModel implements IModel {

  /**
   * @var string
   */
  private $recordClass;
  
  /**
   * @var IDataSource
   */
  private $dataSource;
  
  private $schema;
  
  public final function __construct($recordClass, IDataSource $dataSource, $config = array()) {
    if (!is_subclass_of($recordClass, 'ActiveRecord')) {
      throw new Exception(tr('Invalid record class, must extend ActiveRecord'));
    }
    $this->recordClass = $recordClass;
    $this->dataSource = $dataSource;
    $this->schema = $dataSource->getSchema();
  }
  public function __call($function, $parameters) {
    
  }
  
  public function create($data = array()) {
    $record = new $this->recordClass($this, $data);
  }
  public function all(SelectQuery $query = null) {
  }
  public function first(SelectQuery $query = null) {
    
  }
  public function last(SelectQuery $query = null) {
    
  }
  public function count(SelectQuery $query = null) {
    
  }
}

abstract class ActiveRecord implements IRecord {
  
  public final function __construct(ActiveModel $model, $data = array()) {
    
  }
  
  public function __get($property) {
    
  }
  public function __set($property, $value) {
    
  }
  public function __call($function, $parameters) {
    
  }
  
  public function addData() {
    
  }
  
  public function getModel() {
    
  }
  public function save() {
    
  }
  public function delete() {
    
  }
  public function isNew() {
    
  }
  public function isSaved() {
    
  }
}

class User extends ActiveRecord {
  protected $hasMany = array(
    'Post' => array('thisKey' => 'user_id'),
    'Comment' => array('thisKey' => 'user_id'),
  );

  protected $belongsTo = array(
    'Group' => array('otherKey' => 'group_id'),
  );

  protected $validate = array(
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

  protected $fields = array(
    'username' => 'Username',
    'email' => 'Email',
    'password' => 'Password',
    'confirm_password' => 'Confirm password',
  );

  protected $defaults = array(
    'cookie' => '',
    'session' => '',
    'ip' => '',
    'group_id' => 0,
  );
}

$db = new PdoMysqlDatabase(array(
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

$dataSource = $db->users;

$model = new ActiveModel('User', $dataSource);

$user = $model->first();

if ($user instanceof User) {
  echo 'Great success!';
}
