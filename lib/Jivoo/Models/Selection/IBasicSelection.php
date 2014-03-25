<?php
interface IBasicSelection extends ICondition {
  /**
   * @param string $expression Expression/column
   * @return IBasicSelection
   */
  public function orderBy($expression);

  /**
   * @param string $expression Expression/column
   * @return IBasicSelection
  */
  public function orderByDescending($expression);
  
  /**
   * @return IBasicSelection
   */
  public function reverseOrder();

  /**
   * @param int Number of records
   * @return IBasicSelection
  */
  public function limit($limit);
}