<?php
abstract class DatabaseDriver implements IDatabase {
  protected $tablePrefix = '';

  protected function __construct() {
  }

  public function __destruct() {
    $this->close();
  }

  public function rawQuery($sql) {
    $query = RawQuery::create($sql);
    $query->setDb($this);
    return $query;
  }

  public function insertQuery($table = NULL) {
    $query = InsertQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function selectQuery($table = NULL) {
    $query = SelectQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function deleteQuery($table = NULL) {
    $query = DeleteQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function updateQuery($table = NULL) {
    $query = UpdateQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function createQuery($table = NULL) {
    $query = CreateQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function tableName($table) {
    return $this->tablePrefix . $table;
  }

  public function escapeQuery($format, $vars) {
    $sqlString = '';
    $key = 0;
    $chars = str_split($format);
    if (!is_array($vars)) {
      $vars = func_get_args();
      array_shift($vars);
    }
    foreach ($chars as $offset => $char) {
      if ($char == '?' AND (!isset($chars[$offset - 1]) OR $chars[$offset - 1] != '\\')) {
        if (is_array($vars[$key]) AND isset($vars[$key]['table'])) {
          $sqlString .=  $this->tableName($vars[$key]['table']);
        }
        else if (is_int($vars[$key])) {
          $sqlString .= (int)$vars[$key];
        }
        else if (is_float($vars[$key])) {
          $sqlString .= (float)$vars[$key];
        }
        else {
          $sqlString .= '"' . $this->escapeString($vars[$key]) . '"';
        }
        $key++;
      }
      else if ($char != '\\' OR !isset($chars[$offset + 1]) OR $chars[$offset + 1] != '?') {
        $sqlString .= $char;
      }
    }
    return $sqlString;
  }
}

class DatabaseConnectionFailedException extends Exception { }
class DatabaseSelectFailedException extends Exception { }
class DatabaseQueryFailedException extends Exception { }