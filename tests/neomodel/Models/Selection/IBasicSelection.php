<?php
interface IBasicSelection extends ICondition {
  /**
   * @param unknown $column
   * @return IBasicSelection
   */
  public function orderBy($column);

  /**
   * @param unknown $column
   * @return IBasicSelection
  */
  public function orderByDescending($column);
  
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