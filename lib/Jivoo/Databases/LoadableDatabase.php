<?php
abstract class LoadableDatabase extends Module implements IDatabase, IMigratable {
  
  private $schema;
  
  public final function __construct(App $app, IDatabaseSchema $schema, $options = array()) {
    parent::__construct($app);
    $this->schema = $schema;
    $this->init($options);
  }
  
  protected abstract function init($options);
  
  protected abstract function getTables();
  
  protected abstract function getTableSchema($table);
  
  public function getSchema() {
    return $this->schema;
  }
} 