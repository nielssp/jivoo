<?php
/**
 * Result set for MySQLi driver
 * @package Core\Database\Mysqli
 */
class MysqliResultSet implements IResultSet {
  /**
   * @var mysqli_result MySQLi result object
   */
  private $mysqliResult;
  
  /**
   * @var array[] List of saved rows
   */
  private $rows = array();

  /**
   * Constructor.
   * @param mysqli_result $result MySQLi result object
   */
  public function __construct(mysqli_result $result) {
    $this->mysqliResult = $result;
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
    $row = $this->mysqliResult->fetch_row();
    return $row === null ? false : $row;
  }

  public function fetchAssoc() {
    if (!empty($this->rows)) {
      return array_shift($this->rows);
    }
    $assoc = $this->mysqliResult->fetch_assoc();
    return $assoc === null ? false : $assoc;
  }
}
