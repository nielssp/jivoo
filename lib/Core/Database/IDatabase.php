<?php
interface IDatabase {
  public function __get($table);
  public function __isset($table);
  public function close();
  public function getTable($name);
  public function tableExists($name);
  public function migrate(Schema $schema);
}

class DatabaseConnectionFailedException extends Exception {}
class DatabaseSelectFailedException extends Exception {}
class DatabaseQueryFailedException extends Exception {}

