<?php
class MysqlResultSet implements IResultSet {

  private $mysqlResult;

  public function __construct($result) {
    $this->mysqlResult = $result;
  }

  public function hasRows() {
    return $this->count() > 0;
  }

  public function count() {
    return mysql_num_rows($this->mysqlResult);
  }

  public function fetchRow() {
    return mysql_fetch_row($this->mysqlResult);
  }

  public function fetchAssoc() {
    return mysql_fetch_assoc($this->mysqlResult);
  }
}