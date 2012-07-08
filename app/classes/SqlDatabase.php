<?php
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

  public abstract function getSchema($table);

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
      if ($char == '?' AND (!isset($chars[$offset - 1])
        OR $chars[$offset - 1] != '\\')) {
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
      else if ($char != '\\' OR !isset($chars[$offset + 1])
        OR $chars[$offset + 1] != '?') {
        $sqlString .= $char;
      }
    }
    return $sqlString;
  }

  public function migrate(Schema $schema) {
    $table = $schema->getName();
    if ($this->tableExists($table)) {
      $oldSchema = $this->getSchema($table);
      $allColumns = array_unique(array_merge($schema->getColumns(), $oldSchema->getColumns()));
      $status = 'unchanged';
      foreach ($allColumns as $column) {
        if (!isset($oldSchema->$column)) {
          $this->addColumn($table, $column, $schema->$column);
          $status = 'updated';
        }
        else if (!isset($schema->$column)) {
          $this->deleteColumn($table, $column);
          $status = 'updated';
        }
        else if ($schema->$column != $oldSchema->$column) {
          $this->alterColumn($table, $column, $schema->$column);
          $status = 'updated';
        }
      }
      $indexes = array_keys($schema->indexes);
      $oldIndexes = array_keys($oldSchema->indexes);
      $allIndexes = array_unique(array_merge($indexes, $oldIndexes));
      foreach ($allIndexes as $index) {
        if (!isset($oldSchema->indexes[$index])) {
          $this->createIndex($table, $index, $schema->indexes[$index]);
          $status = 'updated';
        }
        else if (!isset($schema->indexes[$index])) {
          $this->deleteIndex($table, $index);
          $status = 'updated';
        }
        else if ($schema->indexes[$index] != $oldSchema->indexes[$index]) {
          $this->alterIndex($table, $index, $schema->indexes[$index]);
          $status = 'updated';
        }
      }
      return $status;
    }
    else {
      $this->createTable($schema);
      return 'new';
    }
  }

  public abstract function createTable(Schema $schema);

  public abstract function dropTable($table);

  public abstract function addColumn($table, $column, $options = array());

  public abstract function deleteColumn($table, $column);

  public abstract function alterColumn($table, $column, $options = array());

  public abstract function createIndex($table, $index, $options = array());

  public abstract function deleteIndex($table, $index);

  public abstract function alterIndex($table, $index, $options = array());

}

