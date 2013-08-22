<?php
/**
 * A single database table
 * @package Core\Database
 */
interface ITable extends IDataSource {
  /**
   * Get owner database
   * @return IDatabase Owner
   */
  public function getOwner();
}
