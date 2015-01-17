<?php
/**
 * A database schema.
 * @package Jivoo\Databases
 */
interface IDatabaseSchema {
  /**
   * Get table names.
   * @return string[] List of table names.
   */
  public function getTables();
  
  /**
   * Get schema for table.
   * @param string $table Table name.
   * @return ISchema Table schema.
   */
  public function getSchema($table);
}