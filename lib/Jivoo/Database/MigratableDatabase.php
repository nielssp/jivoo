<?php
/**
 * A generic migratable database
 * @package Jivoo\Database
 */
abstract class MigratableDatabase extends Module implements IDatabase, IMigratable {
  
  private $revisionMap;
  
  public final function __construct(App $app, ITableRevisionMap $revisionMap, $options = array()) {
    parent::__construct($app);
    $this->revisionMap = $revisionMap;
    $this->init($options);
  }
  
  protected abstract function init($options = array());

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
    $targetRevision = $schema->getRevision();
    Logger::debug('migration: check ' . $table);
    if ($this->tableExists($table)) {
      $currentRevision = $this->revisionMap->getRevision($table);
      if ($currentRevision != $targetRevision) {
        $schema->migrate($this, $currentRevision);
        return 'updated';
      }
      $result = $this->checkSchema($table, $schema);
      $status = 'unchanged';
      foreach ($result['columns'] as $column => $action) {
        switch ($action) {
          case 'add':
            $this->migrationMethod($schema, 'addColumn', $column)
                or $this->addColumn($table, $column, $schema->$column);
            $status = 'updated';
            break;
          case 'delete':
            $this->migrationMethod($schema, 'deleteColumn', $column)
                or $this->deleteColumn($table, $column);
            $status = 'updated';
            break;
          case 'alter':
            $this->migrationMethod($schema, 'alterColumn', $column)
                or $this->alterColumn($table, $column, $schema->$column);
            $status = 'updated';
            break;
        }
      }
      foreach ($result['indexes'] as $index => $action) {
        switch ($action) {
          case 'add':
            $this->migrationMethod($schema, 'createIndex', $index)
                or $this->createIndex($table, $index, $schema->getIndex($index));
            $status = 'updated';
            break;
          case 'delete':
            $this->migrationMethod($schema, 'deleteIndex', $index)
                or $this->deleteIndex($table, $index);
            $status = 'updated';
            break;
          case 'alter':
            $this->migrationMethod($schema, 'alterIndex', $index)
                or $this->alterIndex($table, $index, $schema->getIndex($index));
            $status = 'updated';
            break;
        }
      }
      return $status;
    }
    else {
      $this->migrationMethod($schema, 'createTable')
        or $this->createTable($schema);
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
