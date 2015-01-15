<?php
/**
 * PDO MySQL database driver
 * @package Jivoo\Database\PdoMysql
 */
class PdoMysqlDatabase extends PdoDatabase {

  /**
   * Constructor.
   * @param array $options An associative array with options for at least
   * 'server', 'username', 'password' and 'database'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException if connection fails
   */
  public function init($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    try {
      if (isset($options['password'])) {
        $this->pdo = new PDO(
          'mysql:host=' . $options['server'] . ';dbname=' . $options['database'],
          $options['username'], $options['password']);
      }
      else {
        $this->pdo = new PDO(
          'mysql:host=' . $options['server'] . ';dbname=' . $options['database'],
          $options['username']);
      }
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
    catch (PDOException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
  }
}
