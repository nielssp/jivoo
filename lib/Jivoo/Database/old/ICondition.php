<?php
/**
 * A condition for selecting rows in a database table
 * @package Jivoo\Database
 * @method ICondition and(Condition|string $clause, mixed $vars,... ) AND operator
 * @method ICondition or(Condition|string $clause, mixed $vars,... ) OR operator
 */
interface ICondition {
  /**
   * Implements methods {@see ICondition::and()} and {@see ICondition::or()}
   * @param string $method Method name ('and' or 'or')
   * @param mixed[] $args List of parameters
   */
  public function __call($method, $args);

  /**
   * If this condition has any clauses
   * @return bool True if more than 0 clauses, false otherwise
   */
  public function hasClauses();

  /**
   * Add clause with AND operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace question marks in
   * $clause with
   * @return ICondition Self
   */
  public function where($clause);

  /**
   * Add clause with AND operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace question marks in
   * $clause with
   * @return ICondition Self
   */
  public function andWhere($clause);

  /**
   * Add clause with OR operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace question marks in
   * $clause with
   * @return ICondition Self
   */
  public function orWhere($clause);

  /**
   * Add value to last clause.
   * 
   * E.g.:
   * <code>
   *  ...->where('id = ?', 2)->and('name = ?')->addVar('test');
   * </code>
   * 'test' will replace the question mark in the last clause: 'name = ?'
   * @param mixed $var Value
   */
  public function addVar($var);
}
