<?php
class PdoResultSet implements IResultSet {

  private $pdoStatement;
  private $rows = array();

  public function __construct(PDOStatement $result) {
    $this->pdoStatement = $result;
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
    return $this->pdoStatement
      ->fetch(PDO::FETCH_NUM);
  }

  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    return $this->pdoStatement
      ->fetch(PDO::FETCH_ASSOC);
  }
}
