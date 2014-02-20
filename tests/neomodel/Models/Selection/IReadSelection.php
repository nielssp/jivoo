<?php
interface IReadSelection extends IBasicSelection, Iterator {
  /**
   * @param string|string[]|array $expression Expression, list of expressions or array of expressions and aliases
   * @param string|alias $alias Alias 
   * @return array[] List of associative arrays
   */
  public function select($expression, $alias = null);
  
  /**
   * Group by one or more columns
   * @param string|string[] $columns A single column name or a list of column
   * names
   * @param Condition|string $condition Grouping condition
   * @return IReadSelection
   */
  public function groupBy($columns, $condition = null);

  // joins
  public function innerJoin(IModel $other, $condition, $alias = null);
  public function leftJoin(IModel $other, $condition, $alias = null);
  public function rightJoin(IModel $other, $condition, $alias = null);

  /**
   * @return IRecord
  */
  public function first();
  /**
   * @return IRecord
  */
  public function last();

  /**
   * @return int
  */
  public function count();
  /**
   * Set offset
   * @param int $offset Offset
   * @return IReadSelection Self
  */
  public function offset($offset);
}