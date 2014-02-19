<?php
class ReadSelection extends BasicSelection implements IReadSelection {

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
   * @param string $column
   * @return IReadSelection
  */
  public function select($column, $alias = null) {
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
  
  public function selectAll() {
    $this->columns = array();
    $this->aliases = array();
    return $this;
  }
  /**
   * @param string|string[] $columns
   * @param string $condition
   * @return IReadSelection
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

  // joins
  public function innerJoin(IModel $dataSource, $condition = null, $alias = null) {
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
  public function leftJoin(IModel $dataSource, $condition, $alias = null) {
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
  public function rightJoin(IModel $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'RIGHT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * @return IRecord
   */
  public function first() {
    $this->model->first($this);
  }

  /**
   * @return IRecord
   */
  public function last() {
    $this->model->last($this);
  }

  /**
   * @return int
   */
  public function count() {
    $this->model->count($this);
  }
  /**
   * Set offset
   * @param int $offset Offset
   * @return IReadSelection Self
   */
  public function offset($offset) {
    $this->offset = (int) $offset;
    return $this;
  }


  function rewind() {
    $this->model->setSelection($this);
    return $this->model->rewind();
  }

  function current() {
    return $this->model->current();
  }

  function key() {
    return $this->model->key();
  }

  function next() {
    return $this->model->next();
  }

  function valid() {
    return $this->model->valid();
  }
}