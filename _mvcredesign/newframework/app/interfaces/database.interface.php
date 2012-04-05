<?php

interface IDatabase {
  public static function connect($server, $username, $password, $database, $options = array());
  public function close();
  public function execute(Query $query);
  public function executeSelect(Query $query);
  public function rawQuery($sql);
  public function insertQuery($table);
  public function selectQuery($table = NULL);
  public function updateQuery($table = NULL);
  public function tableName($table);
  public function count($table, SelectQuery $query = NULL);
  public function tableExists($table);
  public function getColumns($table);
  public function getPrimaryKey($table);
  public function escapeString($string);
  public function escapeQuery($format, $vars);
}
