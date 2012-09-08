<?php
class PdoResultSet implements IResultSet {

  private $pdoStatement;
  private $allRows = array();
  private $pointer = 0;

  public function __construct(PDOStatement $result) {
    $this->pdoStatement = $result;
    $this->allRows = $result->fetchAll(PDO::FETCH_ASSOC);
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
