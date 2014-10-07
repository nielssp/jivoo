<?php
interface IDatabase {
  /**
   * @param string $table Table name
   * @return Table Table
   */
  public function __get($table);
  
  public function __isset($table);
  
  public function close();
  /**
   * @return IDatabaseSchema
   */
  public function getSchema();
  
  public function refreshSchema();
  
  public function beginTransaction();
  
  public function commit();
  
  public function rollback();
}


/**
 * A database connection has failed
 * @package Jivoo\Database
 */
class DatabaseConnectionFailedException extends Exception {}

/**
 * A database selection has failed
 * @package Jivoo\Database
 */
class DatabaseSelectFailedException extends Exception {}

/**
 * A database query has failed
 * @package Jivoo\Database
 */
class DatabaseQueryFailedException extends Exception {}


class TableNotFoundException extends Exception { }