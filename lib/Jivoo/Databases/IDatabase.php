<?php
/**
 * A database.
 * @package Jivoo\Databases
 */
interface IDatabase {
  /**
   * Get table.
   * @param string $table Table name
   * @return Table Table.
   */
  public function __get($table);
  
  /**
   * Whether or not a table exists.
   * @param string $table Table name.
   * @return bool True if table exists, false otherwise.
   */
  public function __isset($table);
  
  /**
   * Close database connection.
   */
  public function close();
  
  /**
   * Get schema of database.
   * @return IDatabaseSchema Database schema.
   */
  public function getSchema();
  
  /**
   * Begin database transaction.
   */
  public function beginTransaction();
  
  /**
   * Commit database transaction.
   */
  public function commit();
  
  /**
   * Rollback database transaction.
   */
  public function rollback();
}

/**
 * A database connection has failed.
 * @package Jivoo\Databases
 */
class DatabaseConnectionFailedException extends Exception {}

/**
 * A database selection has failed.
 * @package Jivoo\Databases
 */
class DatabaseSelectFailedException extends Exception {}

/**
 * A database query has failed.
 * @package Jivoo\Databases
 */
class DatabaseQueryFailedException extends Exception {}

/**
 * A table could not be found.
 * @package Jivoo\Databases
 */
class TableNotFoundException extends Exception { }