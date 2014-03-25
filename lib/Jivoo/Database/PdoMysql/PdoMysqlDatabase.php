<?php
// Database
// Name              : MySQL (PDO)
// Dependencies      : php;pdo_mysql
// Required          : server username database
// Optional          : password tablePrefix
/**
 * PDO MySQL database driver
 * @package Core\Database\PdoMysql
 */
class PdoMysqlDatabase extends PdoDatabase {

  /**
   * Constructor.
   * @param array $options An associative array with options for at least
   * 'server', 'username', 'password' and 'database'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException if connection fails
   */
  public function __construct($options = array()) {
    $this->setTypeAdapter(new MysqlTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    try {
      $this->pdo = new PDO(
        'mysql:host=' . $options['server'] . ';dbname=' . $options['database'],
        $options['username'], $options['password']);
      $this->initTables($this->rawQuery('SHOW TABLES'));
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
    catch (PDOException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
  }
}
