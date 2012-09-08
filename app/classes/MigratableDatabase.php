<?php

abstract class MigratableDatabase implements IDatabase, IMigratable {
  public function migrate(Schema $schema) {
    $table = $schema->getName();
    if ($this->tableExists($table)) {
      $oldSchema = $this->getSchema($table);
      $allColumns = array_unique(array_merge($schema->getColumns(), $oldSchema->getColumns()));
      $status = 'unchanged';
      foreach ($allColumns as $column) {
        if (!isset($oldSchema->$column)) {
          /** @TODO work, work */
          if (method_exists($schema, 'addColumn_' . $column)) {
            call_user_func(array($schema, 'addColumn_' . $column), $this);
          }
          else {
            $this->addColumn($table, $column, $schema->$column);
          }
          $status = 'updated';
        }
        else if (!isset($schema->$column)) {
          $this->deleteColumn($table, $column);
          $status = 'updated';
        }
        else if ($schema->$column != $oldSchema->$column) {
          $this->alterColumn($table, $column, $schema->$column);
          $status = 'updated';
        }
      }
      $indexes = array_keys($schema->indexes);
      $oldIndexes = array_keys($oldSchema->indexes);
      $allIndexes = array_unique(array_merge($indexes, $oldIndexes));
      foreach ($allIndexes as $index) {
        if (!isset($oldSchema->indexes[$index])) {
          $this->createIndex($table, $index, $schema->indexes[$index]);
          $status = 'updated';
        }
        else if (!isset($schema->indexes[$index])) {
          $this->deleteIndex($table, $index);
          $status = 'updated';
        }
        else if ($schema->indexes[$index] != $oldSchema->indexes[$index]) {
          $this->alterIndex($table, $index, $schema->indexes[$index]);
          $status = 'updated';
        }
      }
      return $status;
    }
    else {
      $this->createTable($schema);
      return 'new';
    }
  }
}
