<?php
/**
 * A type and migration adapter.
 */
interface IMigrationTypeAdapter extends IMigratable, ITypeAdapter {
  /**
   * Whether or not a table exists.
   * @param string $table Table name.
   * @return bool True if table exists, false otherwise.
   */
  public function tableExists($table);
  
  /**
   * Get tables.
   * @return string[] List of table names.
   */
  public function getTables();
  
  /**
   * Get table schema.
   * @param string $table Table name.
   * @return ISchema Schema.
   */
  public function getTableSchema($table);
}
