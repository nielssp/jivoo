<?php

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
  public function escapeString($string);
  public function escapeQuery($format, $vars);

  public static function getDriverName();
  public static function getDriverDependencies();
  public static function getRequiredOptions();
}
