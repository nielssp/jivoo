<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

/**
 * A generic PDO SQL database.
 */
abstract class PdoDatabase extends SqlDatabase {
  /**
   * @var PDO PDO Connection.
   */
  protected $pdo;

  /**
   * {@inheritdoc}
   */
  public function close() {
  }

  /**
   * {@inheritdoc}
   */
  public function quoteString($string) {
    return $this->pdo->quote($string);
  }

  /**
   * {@inheritdoc}
   */
  public function rawQuery($sql) {
    Logger::query($sql);
//     Logger::logException(new \Exception());
    $result = $this->pdo->query($sql);
    if (!$result) {
      $errorInfo = $this->pdo->errorInfo();
      throw new DatabaseQueryFailedException(
        $errorInfo[0] . ' - ' . $errorInfo[1] . ' - ' . $errorInfo[2]);
    }
    if (preg_match('/^\\s*(select|show|explain|describe|pragma) /i', $sql)) {
      return new PdoResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return $this->pdo->lastInsertId();
    }
    else {
      return $result->rowCount();
    }
  }

}
