<?php
/**
 * A read selection.
 * @package Jivoo\Models\Selection
 */
interface IReadSelection extends IBasicSelection, IteratorAggregate {
  /**
   * Make a projection.
   * @param string|string[]|array $expression Expression, list of expressions
   * or array of expressions and aliases
   * @param string $alias Alias.
   * @return array[] List of associative arrays
   */
  public function select($expression, $alias = null);
  
  /**
   * @param string|array[] $field Virtual field name (alias) or associative array of field names and expressions
   * @param string $expression Expression to calculate
   * @return IReadSelection A read selection.
   */
  //public function with($field, $expression = null);
  
  /**
   * Group by one or more columns.
   * @param string|string[] $columns A single column name or a list of column
   * names.
   * @param Condition|string $condition Grouping condition.
   * @return IReadSelection A read selection.
   */
  public function groupBy($columns, $condition = null);

  /**
   * Perform an inner join with another model.
   * @param IModel $other Other model.
   * @param string|ICondition $condition Join condition.
   * @param string $alias Alias for joined model/table.
   * @return IReadSelection A read selection.
   */
  public function innerJoin(IModel $other, $condition, $alias = null);
  /**
   * Perform a left join with another model.
   * @param IModel $other Other model.
   * @param string|ICondition $condition Join condition.
   * @param string $alias Alias for joined model/table.
   * @return IReadSelection A read selection.
   */
  public function leftJoin(IModel $other, $condition, $alias = null);

  /**
   * Perform a right join with another model.
   * @param IModel $other Other model.
   * @param string|ICondition $condition Join condition.
   * @param string $alias Alias for joined model/table.
   * @return IReadSelection A read selection.
   */
  public function rightJoin(IModel $other, $condition, $alias = null);

  /**
   * Return first record in selection.
   * @return IRecord|null A record if available..
  */
  public function first();
  /**
   * Return last record in selection.
   * @return IRecord|null A record if available.
  */
  public function last();

  /**
   * Count number of records in selection.
   * @return int Number of records.
  */
  public function count();
  
  /**
   * Convert selection to an array.
   */
  public function toArray();

  /**
   * Set offset.
   * @param int $offset Offset.
   * @return IReadSelection A read selection.
  */
  public function offset($offset);
}
