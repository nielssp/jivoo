<?php
abstract class BasicSelection implements IBasicSelection {
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
  protected $limit = null;

  /**
   * @var Condition Select condition
   */
  protected $where = null;

  /**
   * @var Model
   */
  protected $model = null;

  /**
   * Constructor.
   */
  public function __construct(Model $model) {
    $this->where = new Condition();
    $this->model = $model;
  }

  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
    throw new InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * Check if property is set
   * @param string $property Property name
   * @return bool True if set, false otherwise
   */
  public function __isset($property) {
    return isset($this->$property);
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
    if (is_callable(array($this->model, $method))) {
      return call_user_func(array($this->model, $method), $this);
    }
    throw new InvalidMethodException(tr('Invalid method: %1', $method));
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
   * Convert a basic selection to a full selection. Removes
   * all information specific to read/update/delete.
   * @return Selection Selection
   */
  public function toSelection() {
    $selection = new Selection($this->model);
    $selection->where = $this->where;
    $selection->limit = $this->limit;
    $selection->orderBy = $this->orderBy;
    return $selection;
  }
}
