<?php
// Database
// Name              : MySQL
// Dependencies      : php;mysql
// Required          : server username database
// Optional          : password tablePrefix

/**
 * MySQL database driver
 * @package Jivoo\Database\Mysql
 */
class MysqlDatabase extends SqlDatabase {
  /**
   * @var resource MySQL connection handle
   */
  private $handle;

  /**
   * Constructor.
   * @param array $options An associative array with options for at least
   * 'server', 'username', 'password' and 'database'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException if connection fails
   * @throws DatabaseSelectFailedException if database selection fails
   */
  protected function init($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = mysql_connect($options['server'], $options['username'],
      $options['password'], true);
    if (!$this->handle) {
      throw new DatabaseConnectionFailedException(mysql_error());
    }
    if (!mysql_select_db($options['database'], $this->handle)) {
      throw new DatabaseSelectFailedException(mysql_error());
    }
    try {
      $this->initTables($this->rawQuery('SHOW TABLES'));
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
  }

  public function close() {
    mysql_close($this->handle);
  }

  public function quoteString($string) {
    return '"' . mysql_real_escape_string($string) . '"';
  }

  public function rawQuery($sql) {
    Logger::query($sql);
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
