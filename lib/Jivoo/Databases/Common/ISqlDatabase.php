<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\IDatabase;

/**
 * An SQL database.
 */
interface ISqlDatabase extends IDatabase {
  /**
   * Execute a raw sql query on database.
   * @param string $sql Raw sql.
   * @return IResultSet|int A result set if query is a select-, show-,
   * explain-, or describe-query, the last insert id if query is an insert- or
   * replace-query, or number of affected rows in any other case..
   * @throws DatabaseQueryFailedException if query failed.
   */
  public function rawQuery($sql);

  /**
   * Get type adapter.
   * @return ITypeAdapter Type adapter for database.
   */
  public function getTypeAdapter();
}
