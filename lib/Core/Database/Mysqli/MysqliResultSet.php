<?php
class MysqliResultSet implements IResultSet {

  private $mysqliResult;
  private $rows = array();

  public function __construct(mysqli_result $result) {
    $this->mysqliResult = $result;
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
    $row = $this->mysqliResult->fetch_row();
    return $row === null ? false : $row;
  }

  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    $assoc = $this->mysqliResult->fetch_assoc();
    return $assoc === null ? false : $assoc;
  }
}
