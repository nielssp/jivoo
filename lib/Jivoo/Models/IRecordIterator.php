<?php
/**
 * A record iterator
 * @package Jivoo\Models
 */
interface IRecordIterator extends Iterator {
  /** 
   * Return the current element
   * @return IRecord A record
   */
  public function current();
}