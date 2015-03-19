<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Drivers\PdoSqlite;

use Jivoo\Databases\Common\PdoDatabase;
use Jivoo\Databases\Common\SqliteTypeAdapter;
use Jivoo\Databases\DatabaseQueryFailedException;
use Jivoo\Databases\DatabaseConnectionFailedException;

/**
 * PDO SQLite database driver.
 */
class PdoSqliteDatabase extends PdoDatabase {
  /**
   * Construct database.
   * @param array $options An associative array with options for at least
   * 'filename'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException If connection fails.
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new SqliteTypeAdapter($this));
    if (isset($options['tablePrefix']))
      $this->tablePrefix = $options['tablePrefix'];
    try {
      $this->pdo = new \PDO('sqlite:' . $options['filename']);
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
    catch (\PDOException $exception) {
      throw new DatabaseConnectionFailedException(
        tr('SQLite database does not exist and could not be created: %1',
          $options['filename']));
    }
  }
}
