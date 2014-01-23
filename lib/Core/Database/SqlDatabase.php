<?php
/**
 * A generic SQL database
 * @package Core\Database
 */
abstract class SqlDatabase extends MigratableDatabase implements ISqlDatabase {
  /**
   * @var string Table prefix
   */
  protected $tablePrefix = '';
  
  /**
   * @var array Associative array of table names and {@see SqlTable} objects
   */
  protected $tables = array();

  /**
   * Destructor
   */
  function __destruct() {
    $this->close();
  }
  
  public function __get($name) {
    return $this->tables[$name];
  }

  public function __isset($name) {
    return isset($this->tables[$name]);
//     return $this->tableExists($name);
  }

  public function getTable($name) {
    if (!isset($this->tables[$name])) {
      $this->tables[$name] = new SqlTable($this, $name);
    }
    return $this->tables[$name];
  }

  public function tableName($name) {
    return $this->tablePrefix . $name;
  }

  protected function tableCreated($name) {
    $this->tables[$name] = new SqlTable($this, $name);
  }
  
  /**
   * Initialise table objects based on a result set
   * @param IResultSet $result Result of e.g. a SHOW query on MySQL
   */
  protected function initTables(IResultSet $result) {
    $prefixLength = strlen($this->tablePrefix);
    while ($row = $result->fetchRow()) {
      $name = $row[0];
      if (substr($name, 0, $prefixLength) == $this->tablePrefix) {
        $name = substr($name, $prefixLength);
        $this->tables[$name] = new SqlTable($this, $name);
      }
    }
  }

  /**
   * Escape a string and surround with quotation marks
   * @param string $string String
   */
  public abstract function quoteString($string);

  /**
   * Escape a query
   * @param string $format Query format, use question marks '?' instead of values
   * @param mixed[] $vars List of values to replace question marks with
   * @return string The escaped query
   */
  public function escapeQuery($format, $vars) {
    $sqlString = '';
    $key = 0;
    $chars = str_split($format);
    if (!is_array($vars)) {
      $vars = func_get_args();
      array_shift($vars);
    }
    foreach ($chars as $offset => $char) {
      if ($char == '?'
          AND (!isset($chars[$offset - 1]) OR $chars[$offset - 1] != '\\')) {
        if (is_array($vars[$key]) AND isset($vars[$key]['table'])) {
          $sqlString .= $this->tableName($vars[$key]['table']);
        }
        else if (is_int($vars[$key])) {
          $sqlString .= (int) $vars[$key];
        }
        else if (is_float($vars[$key])) {
          $sqlString .= (float) $vars[$key];
        }
        else if ($vars[$key] === true) {
          $sqlString .= '1';
        }
        else if ($vars[$key] === false) {
          $sqlString .= '0';
        }
        else {
          $sqlString .= $this->quoteString($vars[$key]);
        }
        $key++;
      }
      else if ($char != '\\' OR !isset($chars[$offset + 1])
          OR $chars[$offset + 1] != '?') {
        $sqlString .= $char;
      }
    }
    return $sqlString;
  }
}

