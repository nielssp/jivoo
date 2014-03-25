<?php
/**
 * A generic migratable database
 * @package Jivoo\Database
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
    Logger::debug('migration: ' . $method . ' ' . $subject);
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
    Logger::debug('migration: check ' . $table);
    if ($this->tableExists($table)) {
      $result = $this->checkSchema($table, $schema);
      $status = 'unchanged';
      foreach ($result['columns'] as $column => $action) {
        switch ($action) {
          case 'add':
            $this->migrationMethod($schema, 'addColumn', $column)
                OR $this->addColumn($table, $column, $schema->$column);
            $status = 'updated';
            break;
          case 'delete':
            $this->migrationMethod($schema, 'deleteColumn', $column)
                OR $this->deleteColumn($table, $column);
            $status = 'updated';
            break;
          case 'alter':
            $this->migrationMethod($schema, 'alterColumn', $column)
                OR $this->alterColumn($table, $column, $schema->$column);
            $status = 'updated';
            break;
        }
      }
      foreach ($result['indexes'] as $index => $action) {
        switch ($action) {
          case 'add':
            $this->migrationMethod($schema, 'createIndex', $index)
                OR $this->createIndex($table, $index, $schema->getIndex($index));
            $status = 'updated';
            break;
          case 'delete':
            $this->migrationMethod($schema, 'deleteIndex', $index)
                OR $this->deleteIndex($table, $index);
            $status = 'updated';
            break;
          case 'alter':
            $this->migrationMethod($schema, 'alterIndex', $index)
                OR $this->alterIndex($table, $index, $schema->getIndex($index));
            $status = 'updated';
            break;
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
