<?php
/**
 * A record iterator
 * @package Core\Models
 */
interface IRecordIterator extends Iterator {
  /** 
   * Return the current element
   * @return IRecord A record
   */
  public function current();
}