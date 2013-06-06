<?php
abstract class SqlDatabase extends MigratableDatabase implements ISqlDatabase {
  protected $tablePrefix = '';
  protected $tables = array();

  function __destruct() {
    $this->close();
  }

  public function __get($name) {
    return $this->getTable($name);
  }

  public function __isset($name) {
    return isset($this->tables[$name]);
//     return $this->tableExists($name);
  }

  public function getTable($name) {
//     if (!isset($this->tables[$name])) {
//       $this->tables[$name] = new SqlTable($this, $name);
//     }
    return $this->tables[$name];
  }

  public function tableName($name) {
    return $this->tablePrefix . $name;
  }
  
  protected function tableCreated($name) {
    $this->tables[$name] = new SqlTable($this, $name);
  }

  public abstract function quoteString($string);

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

