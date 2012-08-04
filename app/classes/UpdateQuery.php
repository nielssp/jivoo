<?php
class UpdateQuery extends Query {

  protected $orderBy;
  protected $descending = FALSE;
  protected $limit;
  protected $where;
  protected $whereVars;
  protected $offset = 0;
  protected $sets = array();

  public function set($column, $value = null) {
    if (is_array($column)) {
      foreach ($column as $col => $val) {
        $this->set($col, $val);
      }
    }
    else {
      $this->sets[$column] = $value;
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
}
