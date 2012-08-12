<?php
class SelectQuery extends Query {
  protected $orderBy = array();
  protected $groupBy = NULL;
  protected $groupByCondition = NULL;
  protected $limit;
  protected $where = NULL;
  protected $offset = 0;
  protected $joins = array();
  protected $columns = array();
  protected $aliases = array();
  protected $sources = array();

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

  public function addColumn($column, $alias = NULL, $function = NULL) {
    $this->columns[] = array(
      'column' => $column,
      'function' => $function,
      'alias' => $alias
    );
    if (!empty($alias)) {
      $this->aliases[$column] = $alias;
    }
    return $this;
  }

  public function resetColumns() {
    $this->columns = array();
  }

  public function addSource(IDataSource $source, $alias = NULL) {
    $this->sources[] = array(
      'source' => $source,
      'alias' => $alias
    );
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
    call_user_func_array(array($this->where, 'where'), func_get_args());
    return $this;
  }

  public function andWhere($clause) {
    call_user_func_array(array($this->where, 'andWhere'), func_get_args());
    return $this;
  }

  public function orWhere($clause) {
    call_user_func_array(array($this->where, 'orWhere'), func_get_args());
    return $this;
  }

  public function addVar($var) {
    $this->where->addVar($var);
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy[] = array(
      'column' => $column,
      'descending' => FALSE
    );
    return $this;
  }

  public function orderByDescending($column) {
    $this->orderBy[] = array(
      'column' => $column,
      'descending' => TRUE
    );
    return $this;
  }

  public function reverseOrder() {
    foreach ($this->orderBy as $key => $orderBy) {
      $this->orderBy[$key]['descending'] = !$orderBy['descending'];
    }
    return $this;
  }

  public function groupBy($columns, $condition = NULL) {
    if (!is_array($columns)) {
      $columns = array($columns);
    }
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->groupBy = array(
      'columns' => $columns,
      'condition' => $condition,
    );
    return $this;
  }

  public function join($table, $leftColumn, $rightColumn) {
    $this->innerJoin($table, $leftColumn . ' = %' . $table . '.' . $rightColumn);
    return $this;
  }

  public function innerJoin($dataSource, $condition = NULL, $alias = NULL) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array(
      'source' => $dataSource,
      'type' => 'INNER',
      'alias' => $alias,
      'condition' => $condition
    );
    return $this;
  }

  public function leftJoin($dataSource, $condition, $alias = NULL) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array(
      'source' => $dataSource,
      'type' => 'LEFT',
      'alias' => $alias,
      'condition' => $condition
    );
    return $this;
  }

  public function rightJoin($dataSource, $condition, $alias = NULL) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array(
      'source' => $dataSource,
      'type' => 'RIGHT',
      'alias' => $alias,
      'condition' => $condition
    );
    return $this;
  }

  public function count() {
    $this->resetColumns();
    $this->addColumn('*', NULL, 'COUNT()');
    return $this;
  }

}
