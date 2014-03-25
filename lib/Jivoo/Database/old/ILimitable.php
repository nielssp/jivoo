<?php
/**
 * A query that can be limited
 * @package Jivoo\Database
 */
interface ILimitable {
  /**
   * Set limit, i.e. max number of records
   * @param int $limit Limit
   * @return self Self
   */
  public function limit($limit);
  /**
   * Set offset
   * @param int $offset Offset
   * @return self Self
   */
  public function offset($offset);
}
