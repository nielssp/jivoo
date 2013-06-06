<?php
class SelectQuery extends Query implements ICondition, ILimitable {
  protected $orderBy = array();
  protected $groupBy = null;
  protected $groupByCondition = null;
  protected $limit;
  protected $where = null;
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

  public function addColumn($column, $alias = null, $function = null) {
    $this->columns[] = array('column' => $column, 'function' => $function,
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

  public function addSource(IDataSource $source, $alias = null) {
    $this->sources[] = array('source' => $source, 'alias' => $alias);
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
    $this->limit = (int) $limit;
    return $this;
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

  public function groupBy($columns, $condition = null) {
    if (!is_array($columns)) {
      $columns = array($columns);
    }
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->groupBy = array('columns' => $columns, 'condition' => $condition,);
    return $this;
  }

  public function join(IDataSource $dataSource, $leftColumn, $rightColumn) {
    $this->innerJoin($dataSource, $leftColumn . ' = %' . $dataSource->getName() . '.' . $rightColumn);
    return $this;
  }

  public function innerJoin(IDataSource $dataSource, $condition = null, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'INNER',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  public function leftJoin(IDataSource $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'LEFT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  public function rightJoin(IDataSource $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'RIGHT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  public function count() {
    $this->resetColumns();
    $this->addColumn('*', null, 'COUNT()');
    return $this;
  }

}
