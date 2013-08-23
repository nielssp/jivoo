<?php
/**
 * Result set for MySQL driver
 * @package Core\Database\Mysql
 */
class MysqlResultSet implements IResultSet {
  /**
   * @var resource MySQL result resource
   */
  private $mysqlResult;
  
  /**
   * @var array[] List of saved rows
   */
  private $rows = array();

  /**
   * Constructor
   * @param resource MySQL result resource as returned by {@see mysql_query()}
   */
  public function __construct($result) {
    $this->mysqlResult = $result;
  }

  public function hasRows() {
    return ($this->rows[] = $this->fetchAssoc()) !== false;
  }

  /**
   * Get ordered array from associative array
   * @param array $assoc Associative array
   * @return mixed[] Ordered array
   */
  private function rowFromAssoc($assoc) {
    return array_values($assoc);
  }

  public function fetchRow() {
    if (!empty($this->rows)) {
      return $this->rowFromAssoc(array_shift($this->rows));
    }
    return mysql_fetch_row($this->mysqlResult);
  }

  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    return mysql_fetch_assoc($this->mysqlResult);
  }
}
