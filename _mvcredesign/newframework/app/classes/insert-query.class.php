<?php
class InsertQuery extends Query {

  private $table;

  private $columns = array();
  private $values = array();

  public static function create($table = NULL) {
    $query = new self();
    $query->table = $table;
    return $query;
  }

  public function setTable($table) {
    $this->table = $table;
  }

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
    foreach ($pairs as $column => $value) {
      $this->addColumn($column);
      $this->addValue($value);
    }
    return $this;
  }

  public function toSql(IDatabase $db) {
    $sqlString = 'INSERT INTO ' . $db->tableName($this->table) . ' (';
    $sqlString .= implode(', ', $this->columns);
    $sqlString .= ') VALUES (';
    while (($value= current($this->values)) !== FALSE) {
      if (isset($value)) {
        $sqlString .= $db->escapeQuery('?', $value);
      }
      else {
        $sqlString .= 'NULL';
      }
      if (next($this->values) !== FALSE) {
        $sqlString .= ', ';
      }
    }
    $sqlString .= ')';
    return $sqlString;
  }

}