<?php
require '../lib/Jivoo/Core/bootstrap.php';
Lib::import('Jivoo/Models');
Lib::import('Jivoo/Models/Selection');
Lib::import('Jivoo/Models/Condition');
Lib::import('Jivoo/Models/Validation');

class FileModel implements IModel {
  
  private static $schema = null;
  
  private $dir;
  private $name;
  
  public function __construct($dir) {
    $this->$dir = $dir;
    $this->name = basename($dir);
  }
  
  public function getSchema() {
    if (!isset(self::$schema))
      self::$schema = new FileSchema();
    return self::$schema;
  }
  
  public function insert($data) {
    
  }
  
  public function getName() {
    return $this->name;
  }
}

class FileRecord extends Record {
  
}

error_reporting(E_ALL);

$app = new App('../app/jivoo', '../user/jivoo');

$model = new FileModel($app, '../user/jivoo/media');

var_dump(Logger::getLog());