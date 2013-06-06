<?php

abstract class MigratableDatabase implements IDatabase, IMigratable {
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
      $indexes = array_keys($schema->indexes);
      $oldIndexes = array_keys($oldSchema->indexes);
      $allIndexes = array_unique(array_merge($indexes, $oldIndexes));
      foreach ($allIndexes as $index) {
        if (!isset($oldSchema->indexes[$index])) {
          $this->migrationMethod($schema, 'createIndex', $index)
              OR $this->createIndex($table, $index, $schema->indexes[$index]);
          $status = 'updated';
        }
        else if (!isset($schema->indexes[$index])) {
          $this->migrationMethod($schema, 'deleteIndex', $index)
              OR $this->deleteIndex($table, $index);
          $status = 'updated';
        }
        else if ($schema->indexes[$index] != $oldSchema->indexes[$index]) {
          $this->migrationMethod($schema, 'alterIndex', $index)
              OR $this->alterIndex($table, $index, $schema->indexes[$index]);
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
  
  protected function tableCreated($name) {
  }
}
