<?php
interface IResultSet {
  /**
   * Check if resultset is empty
   * @return bool True if there are rows in resultset
   */
  public function hasRows();
  public function count();
  public function fetchRow();
  public function fetchAssoc();
}