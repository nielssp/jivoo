<?php
/**
 * Query for deleting rows
 * @package Core\Database
 */
class DeleteQuery extends Query implements ICondition {
  protected $orderBy;
  protected $descending = false;
  protected $limit;
  protected $where;
  protected $join;

  public function __construct() {
    $this->where = new Condition();
  }

  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        call_user_func_array(array($this->where, 'andWhere'), $args);
        return $this;
      case 'or':
        call_user_func_array(array($this->where, 'orWhere'), $args);
        return $this;
    }
  }

  public function offset($offset) {
    $this->offset = (int) $offset;
    return $this;
  }

  public function hasClauses() {
    return $this->where
      ->hasClauses();
  }

  public function where($clause) {
    $args = func_get_args();
    call_user_func_array(array($this->where, 'where'), $args);
    return $this;
  }

  public function andWhere($clause) {
    $args = func_get_args();
    call_user_func_array(array($this->where, 'andWhere'), $args);
    return $this;
  }

  public function orWhere($clause) {
    $args = func_get_args();
    call_user_func_array(array($this->where, 'orWhere'), $args);
    return $this;
  }

  public function addVar($var) {
    $this->where
      ->addVar($var);
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy[] = array('column' => $column, 'descending' => false);
    return $this;
  }

  public function orderByDescending($column) {
    $this->orderBy[] = array('column' => $column, 'descending' => true);
    return $this;
  }

  public function reverseOrder() {
    foreach ($this->orderBy as $key => $orderBy) {
      $this->orderBy[$key]['descending'] = !$orderBy['descending'];
    }
    return $this;
  }
}
