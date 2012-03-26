<?php

interface IDatabase {
  public static function connect($server, $username, $password, $options = array());
  public function close();
  public function execute(Query $query);
  public function rawQuery($sql);
  public function insertQuery($table);
  public function selectQuery($table = NULL);
  public function updateQuery($table);
  public function tableName($table);
  public function tableExists($table);
  public function getColumns($table);
  public function escapeString($string);
  public function escapeQuery($format, $vars);
}
