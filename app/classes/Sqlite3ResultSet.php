<?php
class Sqlite3ResultSet implements IResultSet {

  private $result;
  private $allRows = array();
  private $pointer = 0;

  public function __construct(SQLite3Result $result) {
    $this->result = $result;
    while ($row = $result->fetchArray(SQLITE3_BOTH)) {
      $this->allRows[] = $row;
    }
  }

  public function hasRows() {
    return $this->count() > 0;
  }

  public function count() {
    return count($this->allRows);
  }

  public function fetchRow() {
    return $this->allRows[$this->pointer++];
  }

  public function fetchAssoc() {
    return $this->allRows[$this->pointer++];
  }
}
