<?php
class Sqlite3ResultSet implements IResultSet {

  private $result;
  private $rows = array();

  public function __construct(SQLite3Result $result) {
    $this->result = $result;
    while ($row = $result->fetchArray(SQLITE3_BOTH)) {
      $this->allRows[] = $row;
    }
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
    return $this->result->fetchArray(SQLITE3_NUM);
  }

  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    return $this->result->fetchArray(SQLITE3_ASSOC);
  }
}
