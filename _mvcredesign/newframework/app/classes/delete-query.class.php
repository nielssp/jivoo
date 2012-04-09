<?php
class DeleteQuery extends Query {
  protected $orderBy;
  protected $descending;
  protected $limit;
  protected $where;
  protected $whereVars;
  protected $count;
  protected $offset;
  protected $table;
  protected $join;

  public static function create($table = NULL) {
    $query = new self();
    $query->offset = 0;
    $query->descending = FALSE;
    $query->count = false;
    $query->table = $table;
    return $query;
  }

  public function from($table) {
    $this->table = $table;
    return $this;
  }

  public function limit($limit) {
    $this->limit = (int)$limit;
    return $this;
  }

  public function offset($offset) {
    $this->offset = (int)$offset;
    return $this;
  }

  public function where($clause) {
    $this->where = $clause;
    return $this;
  }

  public function addVar($var) {
    $this->whereVars[] = $var;
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy = $column;
    $this->descending = false;
    return $this;
  }

  public function orderByDescending($column) {
    $this->orderBy = $column;
    $this->descending = true;
    return $this;
  }

  public function reverseOrder() {
    $this->descending = !$this->descending;
    return $this;
  }

  public function join($table, $leftColumn, $rightColumn) {
    $this->join = array(
      'table' => $table,
      'left' => $leftColumn,
      'right' => $rightColumn
    );
    return $this;
  }

  public function toSql(IDatabase $db) {
    $sqlString = 'DELETE FROM ' . $db->tableName($this->table);
    if (isset($this->join)) {
      $sqlString .= ' JOIN ' . $db->tableName($this->join['table']);
      $sqlString .= ' ON ' . $this->join['left'] . ' = ' . $this->join['right'];
    }
    if (isset($this->where)) {
      $sqlString .= ' WHERE ' . $db->escapeQuery($this->where, $this->whereVars);
    }
    if (isset($this->orderBy)) {
      $sqlString .= ' ORDER BY ' . $this->orderBy;
      $sqlString .= $this->descending ? ' DESC' : ' ASC';
    }
    if (isset($this->limit)) {
      $sqlString .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
    }
    return $sqlString;
  }
}