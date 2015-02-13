<?php
/**
 * A database implementing migration methods.
 */
interface IMigratableDatabase extends IDatabase, IMigratable {
  /**
   * Refresh schemas, i.e. update database schema to match actual database
   * schema.
   */
  public function refreshSchema();
  
  /**
   * Change schema of database.
   * @param IDatabaseSchema $schema New database schema.
   */
  public function setSchema(IDatabaseSchema $schema);
}