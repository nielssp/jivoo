<?php
abstract class ActiveRecord {
  private static $dbConnection = NULL;
  private static $models = array();
  public static function addModel($class, $table) {
    $db = self::connection();
    self::$models[$class] = array(
      'table' => $table,
      'columns' => $db->getColumns($table),
      'primaryKey' => $db->getPrimaryKey($table)
    );
  }

  private $table;
  private $primaryKey;
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
    $this->primaryKey = self::$models[$class]['primaryKey'];
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

  private static function createFromAssoc($class, $assoc) {
    $new = new $class();
    foreach ($assoc as $property => $value) {
      $new->data[$property] = $value;
    }
    return $new;
  }

  public static function create($data = array()) {
    $db = self::connection();
    $class = get_called_class();
    $new = new $class();
    foreach ($data as $property => $value) {
      $new->data[$property] = $value;
    }
    $query = $db->insertQuery($new->table);
    $new->data[$new->primaryKey] = $query->addPairs($new->data)->execute();
    return $new;
  }

  public function save() {
    $db = self::connection();
    $query = $db->updateQuery($this->table);
    foreach ($this->data as $column => $value) {
      $query->set($column, $value);
    }
    $query->where($this->primaryKey . ' = ?');
    $query->addVar($this->data[$this->primaryKey]);
    $query->execute();
  }

  public static function all(SelectQuery $selector = NULL) {
    $db = self::connection();
    $class = get_called_class();
    if (!isset($selector)) {
      $selector = SelectQuery::create();
    }
    $selector->from(self::$models[$class]['table']);
    $result = $db->executeSelect($selector);
    $allArray = array();
    while ($assoc = $result->fetchAssoc()) {
      $allArray[] = self::createFromAssoc($class, $assoc);
    }
    return $allArray;
  }

  public static function find($primaryKey) {
    $db = self::connection();
    $class = get_called_class();
    $query = $db->selectQuery(self::$models[$class]['table']);
    $query->where(self::$models[$class]['primaryKey'] . ' = ?');
    $query->addVar($primaryKey);
    $query->limit(1);
    $result = $query->execute();
    if (!$result->hasRows()) {
      return FALSE;
    }
    return self::createFromAssoc($class, $result->fetchAssoc());
  }

  public static function exists($primaryKey) {
    $db = self::connection();
    $class = get_called_class();
    $query = $db->selectQuery();
    $query->count();
    $query->where(self::$models[$class]['primaryKey'] . ' = ?');
    $query->addVar($primaryKey);
    return $db->count(self::$models[$class]['table'], $query) > 0;
  }

  public static function first(SelectQuery $selector = NULL) {

  }

  public static function last(SelectQuery $selector = NULL) {

  }

  public static function count(SelectQuery $selector = NULL) {

  }
}

class DatabaseNotConnectedException extends Exception { }