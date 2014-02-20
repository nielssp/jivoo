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
  public function encode(FieldType $type, $value);

  public function decode(FieldType $type, $value);
}

class FieldType {
  const INTEGER = 1;
  const STRING = 2;
  const TEXT = 3;
  const BOOLEAN = 4;
  const FLOAT = 5;
  const DATE = 6;
  const DATETIME = 7;
  const BINARY = 8;
  
  private $type;
  private $null;
  private $length;
  private $unsigned;
  
  private function __construct($type, $null = true, $length = null, $unsigned = false) {
    
  }
  
  public function __get($property) {
    switch ($property) {
      case 'type':
      case 'null':
      case 'length':
      case 'unsigned':
        return $this->$property;
    }
  }
  
  public static function integer($null = true, $unsigned = false) {
    return new self(self::INTEGER, $null, null, $unsigned);
  }
}



abstract class ActiveModel extends Model {
  /**
   * @var Model
   */
  private $source;
  /**
   * @var IDatabase
   */
  private $database;
  
  private $name;
  
  private $schema;
  
  public final function __construct(Model $source, IDatabase $database) {
    $this->source = $source;
    $this->database = $database;
    $this->name = get_class($this);
    $this->schema = $this->source->getSchema();
  }
  
  public function getName() {
    return $this->name;
  }
  
  public function getSchema() {
    return $this->schema;
  }
  
  public function update(UpdateSelection $selection = null) {
    if (!isset($selection))
      $selection = new UpdateSelection($this);
    return $this->source->update($selection);
  }
  
  public function delete(DeleteSelection $selection = null) {
    if (!isset($selection))
      $selection = new DeleteSelection($this);
    return $this->source->delete($selection);
  }
  
  public function count(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    return $this->source->count($selection);
  }
  
  public function first(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    return $this->source->first($selection);
  }
  
  public function last(ReadSelection $selection = null) {
    if (!isset($selection))
      $selection = new ReadSelection($this);
    return $this->source->last($selection);
  }
  
  public function read(ReadSelection $selection) {
    return $this->source->read($selection);
  }

  public function readCustom(ReadSelection $selection) {
    return $this->source->readCustom($selection);
  }
  
  public function insert($data) {
    $this->source->insert($data);
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
    $record = new ActiveRecord($model, $data);
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

class Posts extends ActiveModel {
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
  'server' => 'localhost',
  'username' => 'peanutcms',
  'password' => 'peanutcms',
  'database' => 'peanutcms',
));

$posts = new Posts($db->posts, $db);

echo $posts->innerJoin($db->posts_tags, 'id = post_id')->where('tag_id = 1')->count() . PHP_EOL;

foreach ($posts->innerJoin($db->posts_tags, 'id = post_id')->innerJoin($db->tags, '%tags.id = tag_id')->where('%tags.name = ?', 'test') as $post) {
  echo $post->title . PHP_EOL;
}


// $post->e('title');

// e($post, 'title');

// var_dump(Logger::getLog());