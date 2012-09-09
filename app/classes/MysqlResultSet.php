<?php
class MysqlResultSet implements IResultSet {

  private $mysqlResult;
  private $rows = array();

  public function __construct($result) {
    $this->mysqlResult = $result;
  }

  public function hasRows() {
    return ($this->rows[] = $this->fetchAssoc()) !== false;
  }

  private function rowFromAssoc($assoc) {
    return array_values($assoc);
  }

  public function fetchRow() {
    if (!empty($this->rows)) {
      return $this->rowFromAssoc(array_shift($this->rows));
    }
    return mysql_fetch_row($this->mysqlResult);
  }

  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    return mysql_fetch_assoc($this->mysqlResult);
  }
}
