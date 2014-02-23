<?php
/**
 * Model schema
 * @package Core\Models
 */
interface ISchema {
  /**
   * Get type of column
   * @param string $column Column name
   * @return DataType Type of column
   */
  public function __get($column);

  /**
   * Whether or not a column exists in schema
   * @param string $column Column name
   * @return bool True if it does, false otherwise
   */
  public function __isset($column);

  /**
   * Get name of schema
   * @return string Name
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get columns of primary key
   * @return string[] List of column names or empty array if no primary key
   */
  public function getPrimaryKey();

  /**
   * Get indexes. The 'PRIMARY'-index is the primary key
   * 
   * The returned array is of the following format:
   * <code>
   * array(
   *   'indexname' => array(
   *     'columns' => array('columnname1', 'columnname2'),
   *     'unique' => true
   *   )
   * )
   * </code>
   * @return array Associative array of index names and info
   */
  public function getIndexes();

  /**
   * Check whether or not an index exists
   * @param string $name Index name
   */
  public function indexExists($name);
  
  /**
   * Get information about an index.
   * @param string $name Index name
   * @return array Associative array with two keys: 'columns' is a list of
   * column names and 'unique' is a boolean.
   */
  public function getIndex($name);
  
}
