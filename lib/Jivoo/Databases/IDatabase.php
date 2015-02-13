<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

/**
 * A database.
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
 */
class DatabaseConnectionFailedException extends \Exception {}

/**
 * A database selection has failed.
 */
class DatabaseSelectFailedException extends \Exception {}

/**
 * A database query has failed.
 */
class DatabaseQueryFailedException extends \Exception {}

/**
 * A table could not be found.
 */
class TableNotFoundException extends \Exception { }