<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\Mysqli;

use Jivoo\Databases\Common\SqlDatabase;
use Jivoo\Databases\Common\MysqlTypeAdapter;
use Jivoo\Core\Logger;
use Jivoo\Databases\DatabaseConnectionFailedException;

/**
 * MySQLi database driver.
 */
class MysqliDatabase extends SqlDatabase {
  /**
   * @var mysqli MySQLi object.
   */
  private $handle;

  /**
   * Construct database.
   * @param array $options An associative array with options for at least
   * 'server', 'username', 'password' and 'database'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException If connection fails.
   * @throws DatabaseSelectFailedException If database selection fails.
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = new \mysqli($options['server'], $options['username'],
      $options['password'], $options['database']);
    if ($this->handle->connect_error) {
      throw new DatabaseConnectionFailedException($this->handle->connect_error);
    }
//     try {
//       $this->initTables($this->rawQuery('SHOW TABLES'));
//     }
//     catch (DatabaseQueryFailedException $exception) {
//       throw new DatabaseConnectionFailedException($exception->getMessage());
//     }
  }

  /**
   * {@inheritdoc}
   */
  public function close() {
    $this->handle->close();
  }

  /**
   * {@inheritdoc}
   */
  public function quoteString($string) {
    return '"' . $this->handle->real_escape_string($string) . '"';
  }

  /**
   * {@inheritdoc}
   */
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
