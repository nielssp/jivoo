<?php

include('../app/essentials.php');

interface IDataSource {
  /**
   * @brief The C of CRUD.
   * @parameter InsertQuery $query The insert query
   * @return int The last insert id
   */
  public function insert(InsertQuery $query = null);
  public function select(SelectQuery $query = null);
  public function update(UpdateQuery $query = null);
  public function delete(DeleteQuery $query = null);
  public function count(SelectQuery $query = null);
  public function getColumns();
  public function getPrimaryKey();
}

interface IDatabase {
  public function __construct($options);
  public function __get($table);
  public function __isset($table);
  public function close();
  public function getTable($name);
  public function tableExists($name);
  public function migrate(IMigration $migration);
}

interface ITable extends IDataSource {
  public function getOwner();
}

interface IMigration {
}

class SqlTable implements Itable {
  private $owner = null;
  private $name = '';

  public function __construct(SqlDatabase $database, $table) {
    $this->owner = $database;
    $this->name = $table;
  }

  public function getOwner() {
    return $this->owner;
  }

  public function insert(InsertQuery $query = null) {
    if (!isset($query)) {
      return InsertQuery::create()->setDataSource($this);
    }
    $columns = $query->columns;
    $values = $query->values;
    $sqlString = 'INSERT INTO ' . $this->owner
          ->tableName($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES (';
    while (($value = current($values)) !== false) {
      if (isset($value)) {
        $sqlString .= $this->owner
          ->escapeQuery('?', $value);
      }
      else {
        $sqlString .= 'null';
      }
      if (next($values) !== false) {
        $sqlString .= ', ';
      }
    }
    $sqlString .= ')';
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function select(SelectQuery $query = null) {
    if (!isset($query)) {
      return SelectQuery::create()->setDataSource($this);
    }
    $sqlString = 'SELECT ';
    if (!empty($query->columns)) {
      $sqlString .= $query->count ? 'COUNT(' : '';
      $sqlString .= implode($query->count ? '), COUNT(' : ', ', $query->columns);
      $sqlString .= $query->count ? ')' : '';
    }
    else {
      $sqlString .= $query->count ? 'COUNT(*)' : '*';
    }
    $sqlString .= ' FROM ' . $this->owner
          ->tableName($this->name);
    if (isset($query->join)) {
      $sqlString .= ' JOIN ' . $this->owner
            ->tableName($query->join['table']);
      $sqlString .= ' ON ' . $query->join['left'] . ' = '
          . $query->join['right'];
    }
    if (isset($query->where)) {
      $sqlString .= ' WHERE '
          . $this->owner
            ->escapeQuery($query->where, $query->whereVars);
    }
    if (isset($query->orderBy)) {
      $sqlString .= ' ORDER BY ' . $query->orderBy;
      $sqlString .= $query->descending ? ' DESC' : ' ASC';
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function update(UpdateQuery $query = null) {
    if (!isset($query)) {
      return UpdateQuery::create()->setDataSource($this);
    }
    $sqlString = 'UPDATE ' . $this->owner
          ->tableName($this->name);
    $sets = $query->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET';
      reset($sets);
      while (($value = current($sets)) !== false) {
        $sqlString .= ' '
            . $this->owner
              ->escapeQuery(key($sets) . ' = ?', array($value));
        if (next($sets) !== false) {
          $sqlString .= ',';
        }
      }
    }
    if (isset($query->where)) {
      $sqlString .= ' WHERE '
          . $this->owner
            ->escapeQuery($query->where, $query->whereVars);
    }
    if (isset($query->orderBy)) {
      $sqlString .= ' ORDER BY ' . $query->orderBy;
      $sqlString .= $query->descending ? ' DESC' : ' ASC';
    }
    if (isset($this->query)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function delete(DeleteQuery $query = null) {
    if (!isset($query)) {
      return DelteQuery::create()->setDataSource($this);
    }
    $sqlString = 'DELETE FROM ' . $this->owner
          ->tableName($this->name);
    if (isset($query->join)) {
      $sqlString .= ' JOIN ' . $this->owner
            ->tableName($query->join['table']);
      $sqlString .= ' ON ' . $query->join['left'] . ' = '
          . $query->join['right'];
    }
    if (isset($query->where)) {
      $sqlString .= ' WHERE '
          . $this->owner
            ->escapeQuery($query->where, $query->whereVars);
    }
    if (isset($query->orderBy)) {
      $sqlString .= ' ORDER BY ' . $query->orderBy;
      $sqlString .= $query->descending ? ' DESC' : ' ASC';
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function count(SelectQuery $query = null) {
    if (!isset($query)) {
      $query = new SelectQuery();
    }
    $result = $this->select($query->count());
    if (!$result->hasRows()) {
      return false;
    }
    $row = $result->fetchRow();
    return $row[0];
  }

  public function getcolumns() {
    return $this->owner
      ->getColumns($this->name);
  }

  public function getPrimaryKey() {
    return $this->owner
      ->getPrimaryKey($this->name);
  }
}

abstract class SqlDatabase implements IDatabase {
  protected $tablePrefix = '';
  protected $tables = array();

  public abstract function __construct($options = array());

  function __destruct() {
    $this->close();
  }

  public function __get($name) {
    return $this->getTable($name);
  }

  public function __isset($name) {
    return $this->tableExists($name);
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

  public abstract function getColumns($table);

  public abstract function getPrimaryKey($table);

  public abstract function escapeString($string);

  public abstract function rawQuery($string);

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
          $sqlString .= '"' . $this->escapeString($vars[$key]) . '"';
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

class MysqlDatabase extends SqlDatabase {
  private $handle;

  public function __construct($options = array()) {
    $this->handle = mysql_connect($options['server'], $options['username'],
      $options['password'], true);
    if (!$this->handle) {
      throw new DatabaseConnectionFailedException(mysql_error());
    }
    if (!mysql_select_db($options['database'], $this->handle)) {
      throw new DatabaseSelectFailedException(mysql_error());
    }
  }

  public function close() {
    mysql_close($this->handle);
  }

  public function getColumns($table) {
    $result = $this->rawQuery('SHOW COLUMNS FROM ' . $this->tableName($table));
    $columns = array();
    while ($row = $result->fetchAssoc()) {
      $columns[] = $row['Field'];
    }
    return $columns;
  }

  public function getPrimaryKey($table) {
    $result = $this->rawQuery(
        'SHOW INDEX FROM ' . $this->tableName($table)
            . ' WHERE Key_name = "PRIMARY"');
    $row = $result->fetchAssoc();
    return $row['Column_name'];
  }

  public function escapeString($string) {
    return mysql_real_escape_string($string);
  }

  public function tableExists($table) {
    $result = $this->rawQuery(
        'SHOW TABLES LIKE "' . $this->tableName($table) . '"');
    return $result->count() >= 1;
  }

  public function migrate(IMigration $migration) {}

  public function rawQuery($sql) {
    var_dump($sql);
    $result = mysql_query($sql, $this->handle);
    if (!$result) {
      throw new DatabaseQueryFailedException(mysql_error());
    }
    if (preg_match('/^\\s*(select|show|explain|describe) /i', $sql)) {
      return new MysqlResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return mysql_insert_id($this->handle);
    }
    else {
      return mysql_affected_rows($this->handle);
    }
  }
}

class DatabaseQueryFailedException extends Exception {}
