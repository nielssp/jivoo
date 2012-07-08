<?php
class SelectQuery extends Query {
  protected $orderBy;
  protected $descending = FALSE;
  protected $limit;
  protected $where;
  protected $whereVars;
  protected $count = FALSE;
  protected $offset = 0;
  protected $relation;
  protected $table;
  protected $join;
  protected $columns = array();

  public static function create($table = NULL) {
    $query = new self();
    $query->table = $table;
    return $query;
  }

  public function from($table) {
    $this->table = $table;
    return $this;
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
    if (func_num_args() > 1) {
      $args = func_get_args();
      array_shift($args);
      foreach ($args as $arg) {
        $this->addVar($arg);
      }
    }
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

  public function count() {
    $this->count = TRUE;
    return $this;
  }

  public function toSql(IDatabase $db) {
    $sqlString = 'SELECT ';
    if (!empty($this->columns)) {
      $sqlString .= $this->count ? 'COUNT(' : '';
      $sqlString .= implode($this->count ? '), COUNT(' : ', ', $this->columns);
      $sqlString .= $this->count ? ')' : '';
    }
    else {
      $sqlString .= $this->count ? 'COUNT(*)' : '*';
    }
    $sqlString .= ' FROM ' . $db->tableName($this->table);
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
