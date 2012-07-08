<?php
// Database
// Name              : MySQL
// Dependencies      : php;mysql database>3.6 ext;fancybox<=2.5
// PHPVersion        : 5.2.0
// Required          : server username database
// Optional          : password tablePrefix

class MysqlDatabase extends SqlDatabase {
  private $handle;

  public function __construct($options = array()) {
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = mysql_connect($options['server'], $options['username'], $options['password'], true);
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
    $result = $this->rawQuery('SHOW INDEX FROM ' . $this->tableName($table) . ' WHERE Key_name = "PRIMARY"');
    $row = $result->fetchAssoc();
    return $row['Column_name'];
  }

  public function escapeString($string) {
    return mysql_real_escape_string($string);
  }

  public function tableExists($table) {
    $result = $this->rawQuery('SHOW TABLES LIKE "' . $this->tableName($table) . '"');
    return $result->count() >= 1;
  }

  public function migrate(IMigration $migration) {
  }

  public function rawQuery($sql) {
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
