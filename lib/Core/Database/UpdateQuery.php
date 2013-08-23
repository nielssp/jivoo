<?php
/**
 * A query for updating rows in a database table. All protected attributes in
 * this class are available as public read-only properties thanks to
 * {@see Query::__get()}.
 * @package Core\Database
 */
class UpdateQuery extends Query implements ICondition {
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
   * @var int|null Limit
   */
  protected $limit;
  
  /**
   * @var Condition Select condition
   */
  protected $where = null;
  
  /**
   * @var array Associative array of column names and values
   */
  protected $sets = array();

  /**
   * Constructor.
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
   * Assign value to column, or if $value is null and $column is an array, then
   * assign multiple values to multiple columns.
   * @param string|array $column Column name or associative array of column
   * names and values
   * @param string $value Value
   * @return self Self
   */
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

  /**
   * Limit number of affected rows
   * @param int $limit Limit
   * @return self Self
   */
  public function limit($limit) {
    $this->limit = (int) $limit;
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
}
