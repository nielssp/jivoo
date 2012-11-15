<?php

class Condition implements ICondition {
  private $clauses = array();
  private $vars = array();

  public function __construct($clause = null) {
    if (isset($clause) AND !empty($clause)) {
      $args = func_get_args();
      call_user_func_array(array($this, 'andWhere'), $args);
    }
  }

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

  public function __isset($property) {
    return isset($this->$property);
  }

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
    $this->clauses[] = array(
      'glue' => 'AND',
      'clause' => $clause,
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
    $this->clauses[] = array(
      'glue' => 'OR',
      'clause' => $clause,
      'vars' => $args
    );
    return $this;
  }

  public function addVar($var) {
    $this->clauses[count($this->clauses) - 1]['vars'][] = $var;
    return $this;
  }

}
