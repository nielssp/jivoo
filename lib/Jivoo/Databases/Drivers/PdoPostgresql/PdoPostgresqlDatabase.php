<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\PdoPostgresql;

use Jivoo\Databases\Common\PostgresqlTypeAdapter;
use Jivoo\Databases\Common\PdoDatabase;
use Jivoo\Databases\DatabaseQueryFailedException;
use Jivoo\Databases\DatabaseConnectionFailedException;

/**
 * PDO PostgreSQL database driver.
 */
class PdoPostgresqlDatabase extends PdoDatabase {
  /**
   * Construct database.
   * @param array $options An associative array with options for at least
   * 'server', 'username', 'password' and 'database'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException If connection fails.
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new PostgresqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    try {
      if (isset($options['password'])) {
        $this->pdo = new \PDO(
          'pgsql:host=' . $options['server'] . ';dbname=' . $options['database'],
          $options['username'], $options['password']);
      }
      else {
        $this->pdo = new \PDO(
          'pgsql:host=' . $options['server'] . ';dbname=' . $options['database'],
          $options['username']);
      }
    }
    catch (\PDOException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage(), 0, $exception);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function quoteTableName($name) {
    return '"' . $this->tableName($name) . '"';
  }

  /**
   * {@inheritdoc}
   */
  public function sqlLimitOffset($limit, $offset = null) {
    if (isset($offset))
      return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
    return 'LIMIT ' . $limit;
  }
}
