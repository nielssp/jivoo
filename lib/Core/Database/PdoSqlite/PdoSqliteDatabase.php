<?php
// Database
// Name              : SQLite (PDO)
// Dependencies      : php;pdo_sqlite
// Required          : filename
// Optional          : tablePrefix

/**
 * PDO SQLite database driver
 * @package Core\Database\PdoSqlite
 */
class PdoSqliteDatabase extends PdoDatabase {
  /**
   * Constructor.
   * @param array $options An associative array with options for at least
   * 'filename'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException if connection fails
   */
  public function __construct($options = array()) {
    $this->setTypeAdapter(new  SqliteTypeAdapter($this));
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    try {
      $this->pdo = new PDO('sqlite:' . $options['filename']);
      $this->initTables($this->rawQuery('SELECT name FROM sqlite_master WHERE type = "table"'));
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
    catch (PDOException $exception) {
      throw new DatabaseConnectionFailedException(
        tr('SQLite database does not exist and could not be created: %1',
          $options['filename']));
    }
  }

  public function tableName($name) {
    return strtolower($this->tablePrefix . $name);
  }
}
