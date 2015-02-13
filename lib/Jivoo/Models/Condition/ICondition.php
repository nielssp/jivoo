<?php
/**
 * A condition for selecting rows in a database table
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
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return ICondition Self
   */
  public function where($clause);

  /**
   * Add clause with AND operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return ICondition Self
   */
  public function andWhere($clause);

  /**
   * Add clause with OR operator
   * @param Condition|string $clause Clause
   * @param mixed $vars,... Additional values to replace placeholders in
   * $clause with
   * @return ICondition Self
   */
  public function orWhere($clause);
}
