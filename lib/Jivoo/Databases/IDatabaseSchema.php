<?php
interface IDatabaseSchema {
  /**
   * @return string[]
   */
  public function getTables();
  
  /**
   * @param string $table
   * @return ISchema
   */
  public function getSchema($table);
}