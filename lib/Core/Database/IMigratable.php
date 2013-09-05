<?php
/**
 * A migratable database
 * @package Core\Database
 */
interface IMigratable {
  /**
   * Get schema for table
   * @param string $table Table name
   * @return Schema Table schema
   */
  public function getSchema($table);

  /**
   * Create a table based on a schema
   * @param Schema $schema Schema
   */
  public function createTable(Schema $schema);

  /**
   * Delete a table
   * @param string $table Table name
   */
  public function dropTable($table);

  /**
   * Add a column to a table
   * 
   * Format of options array:
   * <code>
   * array(
   *   'type' => ...,
   *   'length' => ...,
   *   'unsigned' => ...,
   *   'null' => ...,
   *   'default' => ...,
   *   'autoIncrement' => ...
   * )
   * </code>
   * @param string $table Table name
   * @param string $column Column name
   * @param array $options Options
   */
  public function addColumn($table, $column, $options = array());

  /**
   * Delete a column from a table
   * @param string $table Table name
   * @param string $column Column name
   */
  public function deleteColumn($table, $column);

  /**
   * Alter a column in a table
   * 
   * Format of options array:
   * <code>
   * array(
   *   'type' => ...,
   *   'length' => ...,
   *   'unsigned' => ...,
   *   'null' => ...,
   *   'default' => ...,
   *   'autoIncrement' => ...
   * )
   * </code>
   * @param string $table Table name
   * @param string $column Column name
   * @param array $options Options
   */
  public function alterColumn($table, $column, $options = array());

  /**
   * Create an index
   * 
   * Format of options array:
   * <code>
   * array(
   *   'unique' => ..., // Whether or not index is unique (bool)
   *   'columns' => array(...) // List of column names (string[])
   * )
   * </code>
   * @param string $table Table name
   * @param string $index Index name
   * @param array $options Options
   */
  public function createIndex($table, $index, $options = array());

  /**
   * Delete an index
   * @param string $table Table name
   * @param string $index Index name
   */
  public function deleteIndex($table, $index);

  /**
   * Alter an index
   * 
   * Format of options array:
   * <code>
   * array(
   *   'unique' => ..., // Whether or not index is unique (bool)
   *   'columns' => array(...) // List of column names (string[])
   * )
   * </code>
   * @param string $table Table name
   * @param string $index Index name
   * @param array $options Options
   */
  public function alterIndex($table, $index, $options = array());

  /**
   * Alter the primary key
   * 
   * @param string $table Table name
   * @param string[] $columns List of column names
   */
  public function alterPrimaryKey($table, $columns);
}
