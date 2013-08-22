<?php
/**
 * A condition for selecting rows in a database table
 * @package Core\Database
 * @property-read array[] A list of clauses in the form of arrays of the format
 * array('glue' => ..., 'clause' => ..., 'vars' => array(...)) where the glue
 * is either 'AND' or 'OR'. 
 */
class Condition implements ICondition {
  /**
   * @var array[] A list of clauses
   */
  private $clauses = array();

  /**
   * Constructor
   * @param ICondition|string $clause
   * @param mixed $vars,... Additional values to replace question marks in
   * $clause with
   */
  public function __construct($clause = null) {
    if (isset($clause) AND !empty($clause)) {
      $args = func_get_args();
      call_user_func_array(array($this, 'andWhere'), $args);
    }
  }

  /**
   * Get value of property
   * @param string $property Property name
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
  }

  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        return call_user_func_array(array($this, 'andWhere'), $args);
      case 'or':
        return call_user_func_array(array($this, 'orWhere'), $args);
    }
  }

  /**
   * Check if property is set
   * @param string $property Property name
   * @return bool True if set, false otherwise
   */
  public function __isset($property) {
    return isset($this->$property);
  }

  /**
   * Create condition, can be used instead of constructor for chaining purposes
   * @param ICondition|string $clause
   * @param mixed $vars,... Additional values to replace question marks in
   * $clause with
   * @return Condition A new condition
   */
  public static function create() {
    $args = func_get_args();
    $obj = new self();
    call_user_func_array(array($obj, 'andWhere'), $args);
    return $obj;
  }

  public function hasClauses() {
    return count($this->clauses) > 0;
  }

  public function where($clause) {
    $args = func_get_args();
    return call_user_func_array(array($this, 'andWhere'), $args);
  }

  public function andWhere($clause) {
    if (empty($clause)) {
      return $this;
    }
    $args = func_get_args();
    array_shift($args);
    $this->clauses[] = array('glue' => 'AND', 'clause' => $clause,
      'vars' => $args
    );
    return $this;
  }

  public function orWhere($clause) {
    if (empty($clause)) {
      return $this;
    }
    $args = func_get_args();
    array_shift($args);
    $this->clauses[] = array('glue' => 'OR', 'clause' => $clause,
      'vars' => $args
    );
    return $this;
  }

  public function addVar($var) {
    $this->clauses[count($this->clauses) - 1]['vars'][] = $var;
    return $this;
  }

}
