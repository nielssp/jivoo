<?php

require '../../lib/Core/bootstrap.php';
Lib::import('Core');
Lib::import('Core/Database');
Lib::import('Core/Database/PdoMysql');
Lib::import('../tests/neomodel/Models/Condition');
Lib::import('../tests/neomodel/Models/Selection');
Lib::import('../tests/neomodel/Models');
Lib::import('../tests/neomodel/Database');

require 'Models/Condition/ICondition.php';

function where($condition) {
  return new Condition($condition);
}

interface IActiveCollection extends ISelection {
  public function add(IActiveRecord $record);
  public function has(IActiveRecord $record);
  public function remove(IActiveRecord $record);
}

interface ITypeAdapter {
  public function encode($type, $value);

  public function decode($type, $value);
}





abstract class ActiveModel extends Model {
  
  public final function __construct(IModel $source, IDatabase $db) {
    
  }
  
  
}

class ActiveRecord implements IRecord {
  
  private $data = array();
  
  private $updatedData = array();
  /**
   * @var IModel
   */
  private $model;
  
  private $errors = array();
  
  private $new = false;
  private $saved = true;
  
  private function __construct(IModel $model, $data = array(), $allowedFields = null) {
    $this->model = $model;
    $this->addData($data, $allowedFields);
  }
  
  public static function createNew(IModel $model, $data = array(), $allowedFields = null) {
    $record = new ActiveRecord($model, $data, $allowedFields);
    $record->new = true;
    $record->saved = false;
    return $record;
  }
  
  public static function createExisting(IModel $model, $data = array()) {
    $record = new ActiveRecord($model, $data, $allowedFields);
    return $record;
  }
  
  public function getModel() {
    return $this->model;
  }
  
  public function addData($data, $allowedFields = null) {
    if (!is_array($data)) {
      return;
    }
    if (is_array($allowedFields)) {
      $allowedFields = array_flip($allowedFields);
      $data = array_intersect_key($data, $allowedFields);
    }
    foreach ($data as $field => $value) {
      $this->$field = $data[$field];
    }
  }
  
  public function __get($field) {
    return $this->data[$field];
  }
  
  public function __set($field, $value) {
    $this->data[$field] = $value;
    $this->updatedData[$field] = $value;
    $this->saved = false;
  }
  
  public function __isset($field) {
    return isset($this->data[$field]);
  }
  
  public function set($field, $value) {
    $this->$field = $value;
    return $this;
  }
  
  public function isSaved() {
    return $this->saved;
  }
  
  public function isNew() {
    return $this->new;
  }
  
  public function getErrors() {
    return $this->errors;
  }
  
  public function isValid() {
    $validator = $this->model->getValidator();
    $this->errors = $validator->validate($this);
    return count($this->errors) == 0;
  }
  
  public function save($options = array()) {
    $defaultOptions = array('validate' => true);
    $options = array_merge($defaultOptions, $options);
    if ($options['validate'] AND !$this->isValid())
      return false;
    if ($this->isNew()) {
      $this->model->insert($this);
      $this->new = false;
    }
    else if (count($this->updatedData) > 0) {
      $this->model->selectRecord($this)->set($this->updatedData)->update();
    }
    $this->updatedData = array();
    $this->saved = true;
    return true;
  }
  
  public function delete() {
    $this->model->selectRecord($this)->delete();
  }
}

abstract class Posts extends ActiveModel {
  protected $belongsTo = array(
    'category' => 'Categories',
    'category' => array('model' => 'Categories'),
  );
  protected $hasMany = array(
    'Comments',
  );
  protected $hasAndBelongsToMany = array(
    'Tags', // Expectes Posts_Tags table or something for linking
  );
  
  protected function beforeSave(ActiveRecord $post) {
    $post->updatedAt = time();
  }
}

header('Content-Type: text/plain');

class_exists('Table');
class_exists('SqlTable');

$db = new PdoMysqlDatabase(array(
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

$posts= $db->posts;

foreach ($posts as $post) {
  echo $post->title . PHP_EOL;
}

// $post->e('title');

// e($post, 'title');

