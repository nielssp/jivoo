<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Condition;

/**
 * A condition for selecting records in a model.
 * @property-read array[] $clauses A list of clauses in the form of arrays of the format
 * array('glue' => ..., 'clause' => ..., 'vars' => array(...)) where the glue
 * is either 'AND' or 'OR'. 
 */
class Condition implements ICondition {
  /**
   * @var array[] A list of clauses
   */
  private $clauses = array();

  /**
   * Construct condition. The function {@see where} is an alias.
   * @param ICondition|string $clause Clause.
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with.
   */
  public function __construct($clause = null) {
    if (isset($clause) AND !empty($clause)) {
      $args = func_get_args();
      call_user_func_array(array($this, 'andWhere'), $args);
    }
  }

  /**
   * Get value of property.
   * @param string $property Property name.
   * @return mixed Value.
   * @throws InvalidPropertyException If property undefined.
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
    throw new \InvalidPropertyException(tr('Invalid property: %1', $property));
  }

  /**
   * {@inheritdoc}
   */
  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        return call_user_func_array(array($this, 'andWhere'), $args);
      case 'or':
        return call_user_func_array(array($this, 'orWhere'), $args);
    }
    throw new \InvalidMethodException(tr('Invalid method: %1', $method));
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
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return Condition A new condition
   */
  public static function create() {
    $args = func_get_args();
    $obj = new self();
    call_user_func_array(array($obj, 'andWhere'), $args);
    return $obj;
  }

  /**
   * {@inheritdoc}
   */
  public function hasClauses() {
    return count($this->clauses) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function where($clause) {
    $args = func_get_args();
    return call_user_func_array(array($this, 'andWhere'), $args);
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
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

  /**
   * Add value of placeholder.
   * @param mixed $var Value.
   * @return self Self.
   */
  public function addVar($var) {
    $this->clauses[count($this->clauses) - 1]['vars'][] = $var;
    return $this;
  }

  /**
   * Escape string for use with the SQL LIKE operator.
   * @param string $string String.
   * @return string Escaped string.
   */
  public static function escapeLike($string) {
    return str_replace(
      array('%', '_'),
      array('\\%', '\\_'),
      str_replace('\\', '\\\\', $string)
    );
  }
}
