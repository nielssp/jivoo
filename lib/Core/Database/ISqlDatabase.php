<?php
/**
 * An SQL database
 * @package Core\Database
 */
interface ISqlDatabase extends IDatabase {
  /**
   * Execute a raw sql query on database
   * @param string $sql Raw sql
   * @return IResultSet|int A result set if query is a select-, show-,
   * explain-, or describe-query, the last insert id if query is an insert- or
   * replace-query, or number of affected rows in any other case.
   * @throws DatabaseQueryFailedException if query failed
   */
  public function rawQuery($sql);
}
