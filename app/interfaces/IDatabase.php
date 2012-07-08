<?php
interface IDatabase {
  public function __get($table);
  public function __isset($table);
  public function close();
  public function getTable($name);
  public function tableExists($name);
  public function migrate(Schema $schema);
}

class DatabaseConnectionFailedException extends Exception { }
class DatabaseSelectFailedException extends Exception { }
class DatabaseQueryFailedException extends Exception { } 

/*
interface IDatabase {
  public static function connect($options = array());
  public function close();
  public function execute(Query $query);
  public function executeSelect(Query $query);
  public function rawQuery($sql);
  public function insertQuery($table = NULL);
  public function selectQuery($table = NULL);
  public function deleteQuery($table = NULL);
  public function updateQuery($table = NULL);
  public function createQuery($table = NULL);
  public function tableName($table);
  public function count($table, SelectQuery $query = NULL);
  public function tableExists($table);
  public function getColumns($table);
  public function getPrimaryKey($table);
  public function getIndexes($table);
  public function escapeString($string);
  public function escapeQuery($format, $vars);
} */
