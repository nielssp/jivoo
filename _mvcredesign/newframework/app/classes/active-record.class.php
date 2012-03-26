<?php
abstract class ActiveRecord {
  private static $dbConnection = NULL;
  private static $models = array();
  public static function addModel($class, $table) {
    self::$models[$class] = array('table' => $table);
  }

  private $table;
  private $data;

  public function __set($property, $value) {
    if (array_key_exists($property, $this->data)) {
      $this->data[$property] = $value;
    }
    else {

    }
  }

  public function __get($property) {
    if (array_key_exists($property, $this->data)) {
      return $this->data[$property];
    }
    else {

    }
  }

  private function __construct() {
    $db = self::connection();
    $class = get_class($this);
    $this->table = self::$models[$class]['table'];
    if (!isset(self::$models[$class]['columns'])) {
      self::$models[$class]['columns'] = $db->getColumns($this->table);
      //       foreach ($columns as $column) {
      //         $fieldArr = explode('_', $column);
      //         $field = $fieldArr[count($fieldArr) - 1];
      //         self::$models[$class]['columns'][$column] = $field;
      //       }
    }
    $this->data = array();
    foreach (self::$models[$class]['columns'] as $column) {
      $this->data[$column] = NULL;
    }
  }

  protected static function connection() {
    if (!isset(self::$dbConnection)) {
      throw new DatabaseNotConnectedException('ActiveRecord is not connected to a database.');
    }
    return self::$dbConnection;
  }

  public static function connect(IDatabase $db) {
    self::$dbConnection = $db;
  }

  public static function create($data = array()) {
    $db = self::connection();
    $class = get_called_class2();
    $new = new $class();
    foreach ($data as $property => $value) {
      $new->$property = $value;
    }
    $query = $db->insertQuery($new->table);
    //     foreach (self::$models[$class]['columns'] as $column => $field) {
    //       if (isset($new->data[$field])) {
    //         $query->addPair($column, $new->data[$field]);
    //       }
    //     }
    $new->id = $query->addPairs($new->data)->execute();
    return $new;
  }

  public function save() {

  }

  public function all(SelectQuery $selector = NULL) {

  }

  public function find($primaryKey = NULL) {

  }

  public function exists($primaryKey) {

  }

  public function first(SelectQuery $selector = NULL) {

  }

  public function last(SelectQuery $selector = NULL) {

  }

  public function count(SelectQuery $selector = NULL) {

  }
}

class DatabaseNotConnectedException extends Exception { }