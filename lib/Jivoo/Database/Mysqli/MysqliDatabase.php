<?php
// Database
// Name              : MySQLi
// Dependencies      : php;mysqli
// Required          : server username database
// Optional          : password tablePrefix

/**
 * MySQLi database driver
 * @package Jivoo\Database\Mysqli
 */
class MysqliDatabase extends SqlDatabase {
  /**
   * @var mysqli MySQLi object
   */
  private $handle;

  /**
   * Constructor.
   * @param array $options An associative array with options for at least
   * 'server', 'username', 'password' and 'database'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException if connection fails
   * @throws DatabaseSelectFailedException if database selection fails
   */
  public function __construct($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = new mysqli($options['server'], $options['username'],
      $options['password'], $options['database']);
    if ($this->handle->connect_error) {
      throw new DatabaseConnectionFailedException($this->handle->connect_error);
    }
    try {
      $this->initTables($this->rawQuery('SHOW TABLES'));
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
  }

  public function close() {
    $this->handle->close();
  }

  public function quoteString($string) {
    return '"' . $this->handle->real_escape_string($string) . '"';
  }

  public function rawQuery($sql) {
    Logger::query($sql);
    $result = $this->handle->query($sql);
    if (!$result) {
      throw new DatabaseQueryFailedException($this->handle->error);
    }
    if (preg_match('/^\\s*(select|show|explain|describe) /i', $sql)) {
      return new MysqliResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return $this->handle->insert_id;
    }
    else {
      return $this->handle->affected_rows;
    }
  }
}
