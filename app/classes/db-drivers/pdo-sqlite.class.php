<?php
class PdoSqlite extends DatabaseDriver {

  private $handle;

  public static function connect($options = array()) {
    $db = new self();
    $db->handle = mysql_connect($options['server'], $options['username'], $options['password'], true);
    if (!$db->handle) {
      throw new DatabaseConnectionFailedException(mysql_error());
    }
    if (!mysql_select_db($options['database'], $db->handle)) {
      throw new DatabaseSelectFailedException(mysql_error());
    }
    return $db;
  }

  public function __destruct() {
  }

  public function close() {
    mysql_close($this->handle);
  }

  private function mysqlQuery($sql) {
    $result = mysql_query($sql, $this->handle);
    if (!$result) {
      throw new DatabaseQueryFailedException(mysql_error());
    }
    return $result;
  }

  public function execute(Query $query) {
    $sql = $query->toSql($this);
    /** @todo It would be cool if we could detect potential sql injections here */
    $mysqlResult = $this->mysqlQuery($sql);
    if (preg_match('/^\\s*(select|show|explain|describe) /i', $sql)) {
      return new MysqlResultSet($mysqlResult);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return mysql_insert_id($this->handle);
    }
    else {
      return mysql_affected_rows($this->handle);
    }
  }

  public function executeSelect(Query $query) {
    return new MysqlResultSet($this->mysqlQuery($query->toSql($this)));
  }

  public function count($table, SelectQuery $query = NULL) {
    if (!isset($query)) {
      $query = $this->selectQuery($table)->count();
    }
    else {
      $query->from($table)->count();
    }
    $result = $this->executeSelect($query);
    if (!$result->hasRows()) {
      return FALSE;
    }
    $row = $result->fetchRow();
    return $row[0];
  }

  public function tableExists($table) {
    $result = $this->mysqlQuery("SHOW TABLES LIKE '" . $this->tableName($table) . "'");
    if (mysql_num_rows($result) >= 1)
      return true;
    else
      return false;
  }

  public function getColumns($table) {
    $result = $this->mysqlQuery("SHOW COLUMNS FROM `" . $this->tableName($table) . "`");
    $columns = array();
    while ($row = mysql_fetch_array($result)) {
      $columns[] = $row['Field'];
    }
    return $columns;
  }

  public function getPrimaryKey($table) {
    $result = $this->mysqlQuery('SHOW INDEX FROM ' . $this->tableName($table) . ' WHERE Key_name = "PRIMARY"');
    $row = mysql_fetch_array($result);
    return $row['Column_name'];
  }

  public function escapeString($string) {
    return mysql_real_escape_string($string);
  }

  public static function getDriverName() {
    return 'SQLite (PDO)';
  }

  public static function getDriverDependencies() {
    return array('pdo_sqlite', 'pdo');
  }

  public static function getRequiredOptions() {
    return array('database');
  }


}