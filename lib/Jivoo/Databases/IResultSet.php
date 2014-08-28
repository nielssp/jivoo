<?php
/**
 * Represents a database query result
 * @package Jivoo\Database
 */
interface IResultSet {

  /**
   * Check if resultset is empty
   * @return bool True if there are rows in resultset
   */
  public function hasRows();
  
  /**
   * Fetch the next row as an ordered array
   * @return mixed[]|false The array or false if no more rows
   */
  public function fetchRow();
  
  /**
   * Fetch the next row as an associative array
   * @return array|false The array or false if no more rows
   */
  public function fetchAssoc();
}
