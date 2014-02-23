<?php
/**
 * A query to retrieve rows in a database table. All protected attributes in
 * this class are available as public read-only properties thanks to
 * {@see Query::__get()}.
 * @package Core\Database
 */
class SelectQuery extends Query implements ICondition, ILimitable {
  /**
   * List of arrays describing ordering.
   * 
   * Each array is of the format:
   * <code>
   * array(
   *   'column' => ..., // Column name (string)
   *   'descending' => .... // Whether or not to order in descending order (bool)
   * )
   * </code>
   * @var array[]
   */
  protected $orderBy = array();
  
  /**
   * An associative array describing group by.
   * 
   * The array is of the following format:
   * <code>
   * array(
   *   'columns' => array(...), // List of column names (string[])
   *   'condition' => ... // Group condition ({@see Condition})
   * )
   * </code>
   * @var array|null
   */
  protected $groupBy = null;
  
  /**
   * @var int|null Limit
   */
  protected $limit;
  
  /**
   * @var Condition Select condition
   */
  protected $where = null;
  
  /**
   * @var int Offset
   */
  protected $offset = 0;
  
  /**
   * List of arrays describing joins.
   * 
   * Each array is of the following format:
   * <code>
   * array(
   *   'source' => ..., // Data source to join with ({@see IDataSource})
   *   'type' => ..., // Type of join: 'INNER', 'RIGHT' or 'LEFT'
   *   'alias' => ..., // Alias for other data source (string|null)
   *   'condition' => ... // Join condition ({@see Condition}) 
   * );
   * </code>
   * @var array[]
   */
  protected $joins = array();
  
  /**
   * List of arrays describing columns.
   * 
   * Each array is of the following format:
   * <code>
   * array(
   *   'column' => ..., // Column name (string)
   *   'function' => ...,  // Function (string|null)
   *   'alias' => ... // Alias (string|null)
   * )
   * </code>
   * @var array[]
   */
  protected $columns = array();
  
  /**
   * @var array An associative array of column names and aliases
   */
  protected $aliases = array();
  
  /**
   * List of arrays describing other data sources.
   * 
   * Each array is of the following format:
   * <code>
   * array(
   *   'source' => ..., // Other data source ({@see IDatasource})
   *   'alias' => ... // Alias to use for data source (string)
   * )
   * </code>
   * @var array[]
   */
  protected $sources = array();

  /**
   * Constructor
   */
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

  /**
   * Add a column to select query
   * @param string $column Column name
   * @param string $alias Alias to use for column in rest of query
   * @param string $function A function to be used on column, '()' is a
   * placeholder for the column, e.g. 'MAX()'.
   * @return self Self
   */
  public function addColumn($column, $alias = null, $function = null) {
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
  
  /**
   * Reset selected columns to '*', i.e. all columns
   * @return self Self
   */
  public function resetColumns() {
    $this->columns = array();
    return $this;
  }

  /**
   * Add another data source to the select query
   * @param IDataSource $source Other data source
   * @param string $alias Alias to use for data source in rest of query
   * @return self Self
   */
  public function addSource(IDataSource $source, $alias = null) {
    $this->sources[] = array('source' => $source, 'alias' => $alias);
    return $this;
  }

  /**
   * Add multiple columns to select query (default is *, i.e. all columns)
   * @param string|string[] $columns A single column or a list of columns
   * @return self Self
   */
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
    $this->where->addVar($var);
    return $this;
  }

  /**
   * Order by a column in ascending order
   * @param string $column Column name
   * @return self Self
   */
  public function orderBy($column) {
    $this->orderBy[] = array('column' => $column, 'descending' => false);
    return $this;
  }

  /**
   * Order by a column in descending order
   * @param string $column Column name
   * @return self Self
   */
  public function orderByDescending($column) {
    $this->orderBy[] = array('column' => $column, 'descending' => true);
    return $this;
  }

  /**
   * Reverse order of all orderBy's
   * @return self Self
   */
  public function reverseOrder() {
    foreach ($this->orderBy as $key => $orderBy) {
      $this->orderBy[$key]['descending'] = !$orderBy['descending'];
    }
    return $this;
  }

  /**
   * Group by one or more columns
   * @param string|string[] $columns A single column name or a list of column
   * names
   * @param Condition|string $condition Grouping condition
   * @return self Self
   */
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

  /**
   * An inner join on the columns defined by $leftColumn and $rightColumn
   * @param IDataSource $dataSource Other data source to join with
   * @param string $leftColumn Column name 1
   * @param unknown $rightColumn Column name 2
   * @return self Self
   */
  public function join(IDataSource $dataSource, $leftColumn, $rightColumn) {
    $this->innerJoin($dataSource, $leftColumn . ' = %' . $dataSource->getName() . '.' . $rightColumn);
    return $this;
  }

  /**
   * An inner join 
   * @param IDataSource $dataSource Other data source to join with
   * @param Condition|string $condition Join condition
   * @param string $alias Alias to use for other data source in rest of query
   * @return self Self
   */
  public function innerJoin(IDataSource $dataSource, $condition = null, $alias = null) {
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

  /**
   * A left join
   * @param IDataSource $dataSource Other data source to join with
   * @param Condition|string $condition Join condition
   * @param string $alias Alias to use for other data source in rest of query
   * @return self Self
   */
  public function leftJoin(IDataSource $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'LEFT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * A right join
   * @param IDataSource $dataSource Other data source to join with
   * @param Condition|string $condition Join condition
   * @param string $alias Alias to use for other data source in rest of query
   * @return self Self
   */
  public function rightJoin(IDataSource $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'RIGHT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * Convert query to a counting query
   * @return self Self
   */
  public function count() {
    $this->resetColumns();
    $this->addColumn('*', null, 'COUNT()');
    return $this;
  }

}
