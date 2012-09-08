<?php
class MysqliResultSet implements IResultSet {

  private $mysqliResult;

  public function __construct(mysqli_result $result) {
    $this->mysqliResult = $result;
  }

  public function hasRows() {
    return $this->count() > 0;
  }

  public function count() {
    return $this->mysqliResult->num_rows;
  }

  public function fetchRow() {
    return $this->mysqliResult->fetch_row();
  }

  public function fetchAssoc() {
    return $this->mysqliResult->fetch_assoc();
  }
}
