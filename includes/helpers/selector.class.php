<?php
class Selector {
  private $orderBy;
  private $descending;
  private $limit;
  private $where;
  private $offset;

  /* Properties begin */
  private $_getters = array('orderBy', 'descending', 'limit', 'where', 'offset');
  private $_setters = array();

  /**
   * Magic method
   * @param string $property
   * @throws Exception
   */
  public function __get($property) {
    if (in_array($property, $this->_getters)) {
      return $this->$property;
    }
    else if (method_exists($this, '_get_' . $property))
    return call_user_func(array($this, '_get_' . $property));
    else if (in_array($property, $this->_setters) OR method_exists($this, '_set_' . $property))
    throw new Exception('Property "' . $property . '" is write-only.');
    else
    throw new Exception('Property "' . $property . '" is not accessible.');
  }

  public function __set($property, $value) {
    if (in_array($property, $this->_setters)) {
      $this->$property = $value;
    }
    else if (method_exists($this, '_set_' . $property))
    call_user_func(array($this, '_set_' . $property), $value);
    else if (in_array($property, $this->_getters) OR method_exists($this, '_get_' . $property))
    throw new Exception('Property "' . $property . '" is read-only.');
    else
    throw new Exception('Property "' . $property . '" is not accessible.');
  }

  private function _get_ascending() {
    return !$this->descending;
  }
  /* Properties end */

  private function __construct() {
    $this->limit = -1;
    $this->offset = 0;
    $this->where = array();
    $this->orderBy = 'id';
    $this->descending = false;
  }

  public static function create() {
    return new self();
  }

  public function limit($limit) {
    $this->limit = $limit;
    return $this;
  }

  public function offset($offset) {
    $this->offset = $offset;
    return $this;
  }

  public function where($column, $value) {
    $this->where[$column] = $value;
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy = $column;
    return $this;
  }

  public function desc() {
    $this->descending = true;
    return $this;
  }

  public function asc() {
    $this->descending = false;
    return $this;
  }
}