<?php
/**
 * A generic migratable database
 * @package Core\Database
 */
abstract class MigratableDatabase implements IDatabase, IMigratable {
  /**
   * Call migration method
   * @param Schema $schema Table schema
   * @param string $method Method name
   * @param string $subject Subject (column name or index name
   * @return boolean
   */
  private function migrationMethod(Schema $schema, $method, $subject = '') {
    if (!empty($subject)) {
      $method = $method . '_' . $subject;
    }
    if (!method_exists($schema, $method)) {
      return false;
    }
    call_user_func(array($schema, $method), $this);
    return true;
  }

  public function migrate(Schema $schema) {
    $table = $schema->getName();
    if ($this->tableExists($table)) {
      $oldSchema = $this->getSchema($table);
      $allColumns = array_unique(
        array_merge($schema->getColumns(), $oldSchema->getColumns()));
      $status = 'unchanged';
      foreach ($allColumns as $column) {
        if (!isset($oldSchema->$column)) {
          $this->migrationMethod($schema, 'addColumn', $column)
              OR $this->addColumn($table, $column, $schema->$column);
          $status = 'updated';
        }
        else if (!isset($schema->$column)) {
          $this->migrationMethod($schema, 'deleteColumn', $column)
              OR $this->deleteColumn($table, $column);
          $status = 'updated';
        }
        else if ($schema->$column != $oldSchema->$column) {
          $this->migrationMethod($schema, 'alterColumn', $column)
              OR $this->alterColumn($table, $column, $schema->$column);
          $status = 'updated';
        }
      }
      $primaryKey = $schema->getPrimaryKey();
      $oldPrimaryKey = $oldSchema->getPrimaryKey();
      if ($primaryKey != $oldPrimaryKey) {
        $this->migrationMethod($schema, 'alterPrimaryKey')
            OR $this->alterPrimaryKey($table, $primarykey);
      } 
      $indexes = array_keys($schema->getIndexes());
      $oldIndexes = array_keys($oldSchema->getIndexes());
      $allIndexes = array_unique(array_merge($indexes, $oldIndexes));
      foreach ($allIndexes as $index) {
        if (!$oldSchema->indexExists($index)) {
          $this->migrationMethod($schema, 'createIndex', $index)
              OR $this->createIndex($table, $index, $schema->getIndex($index));
          $status = 'updated';
        }
        else if (!$schema->indexExists($index)) {
          $this->migrationMethod($schema, 'deleteIndex', $index)
              OR $this->deleteIndex($table, $index);
          $status = 'updated';
        }
        else if ($schema->getIndex($index) != $oldSchema->getIndex($index)) {
          $this->migrationMethod($schema, 'alterIndex', $index)
              OR $this->alterIndex($table, $index, $schema->getIndex($index));
          $status = 'updated';
        }
      }
      return $status;
    }
    else {
      $this->migrationMethod($schema, 'createTable')
          OR $this->createTable($schema);
      $this->tableCreated($schema->getName());
      return 'new';
    }
  }
  
  /**
   * Called after a table has been created
   * @param string $name Table name
   */
  protected function tableCreated($name) {
  }
}
