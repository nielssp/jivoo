<?php
/**
 * The most basic selection.
 * @package Jivoo\Models\Selection
 */
interface IBasicSelection extends ICondition {
  /**
   * Order selection by a column or expression.
   * @param string $expression Expression or column.
   * @return IBasicSelection A selection.
   */
  public function orderBy($expression);

  /**
   * Order selection by a column or expression, in descending order.
   * @param string $expression Expression/column
   * @return IBasicSelection A selection.
  */
  public function orderByDescending($expression);
  
  /**
   * Reverse the ordering.
   * @return IBasicSelection A selection.
   */
  public function reverseOrder();

  /**
   * Limit number of records.
   * @param int Number of records.
   * @return IBasicSelection A selection.
  */
  public function limit($limit);
}