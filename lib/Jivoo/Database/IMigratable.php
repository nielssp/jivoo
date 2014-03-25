<?php
/**
 * A migratable database
 * @package Core\Database
 */
interface IMigratable {
  /**
   * Check schema for table
   * @param string $table Table name
   * @param ISchema $schema Schema
   * @return array An associative array consisting of two keys, 'indexes' and 'columns',
   * each containing an associative array of
   * names mapped to actions (one of 'add', 'delete', 'alter' and 'ok').
   */
  public function checkSchema($table, ISchema $schema);

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
   * @param string $table Table name
   * @param string $column Column name
   * @param DataType $type Type
   */
  public function addColumn($table, $column, DataType $type);

  /**
   * Delete a column from a table
   * @param string $table Table name
   * @param string $column Column name
   */
  public function deleteColumn($table, $column);

  /**
   * Alter a column in a table
   * @param string $table Table name
   * @param string $column Column name
   * @param DataType $type Type
   */
  public function alterColumn($table, $column, DataType $type);

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
}
