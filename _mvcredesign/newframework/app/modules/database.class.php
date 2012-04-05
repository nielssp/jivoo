<?php
class Database extends DatabaseDriver implements IModule {
  private $configuration;
  private $driver;
  private $connection;

  public function __construct($configuration = NULL) {
    $this->driver = $configuration->get('database.driver');
    require_once(p(CLASSES . 'db-drivers/' . $this->driver . '.class.php'));
    $this->connection = call_user_func(
      array(fileClassName($this->driver), 'connect'),
      $configuration->get('database.server'),
      $configuration->get('database.username'),
      $configuration->get('database.password'),
      $configuration->get('database.database')
    );
    if ($configuration->exists('database.table_prefix')) {
      $this->tablePrefix = $configuration->get('database.table_prefix');
      $this->connection->tablePrefix = $this->tablePrefix;
    }
    ActiveRecord::connect($this);
  }

  public static function getDependencies() {
    return array('configuration');
  }

  public function close() {
    if ($this->connection)
      $this->connection->close();
  }

  public static function connect($server, $username, $password, $database, $options = array()) {

  }

  public function execute(Query $query) {
    return $this->connection->execute($query);
  }

  public function tableExists($table) {
    return $this->connection->tableExists($table);
  }

  public function getColumns($table) {
    return $this->connection->getColumns($table);
  }

  public function escapeString($string) {
    return $this->connection->escapeString($string);
  }

}
