<?php
/**
 * A single database table
 * @package Jivoo\Database
 */
interface ITable extends IDataSource {
  /**
   * Get owner database
   * @return IDatabase Owner
   */
  public function getOwner();
}
