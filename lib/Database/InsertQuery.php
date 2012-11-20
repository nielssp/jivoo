<?php
class InsertQuery extends Query {

  protected $columns = array();
  protected $values = array();

  public function addColumn($column) {
    $this->columns[] = $column;
    return $this;
  }

  public function addColumns($columns) {
    if (!is_array($columns)) {
      $columns = func_get_args();
    }
    foreach ($columns as $column) {
      $this->addColumn($column);
    }
    return $this;
  }

  public function addValue($value) {
    $this->values[] = $value;
    return $this;
  }

  public function addValues($values) {
    if (!is_array($values)) {
      $values = func_get_args();
    }
    foreach ($values as $value) {
      $this->addValue($value);
    }
    return $this;
  }

  public function addPair($column, $value) {
    $this->addColumn($column);
    $this->addValue($value);
    return $this;
  }

  public function addPairs($pairs) {
    if (is_array($pairs)) {
      foreach ($pairs as $column => $value) {
        $this->addColumn($column);
        $this->addValue($value);
      }
    }
    return $this;
  }

}
