<?php
// Database
// Name              : SQLite3
// Dependencies      : php;sqlite3
// Required          : filename
// Optional          : tablePrefix

/**
 * SQLite3 database driver
 * @package Core\Database\Sqlite3
 */
class Sqlite3Database extends SqlDatabase {
  /**
   * @var SQLite3 SQLite3 object
   */
  private $handle;

  /**
   * Constructor.
   * @param array $options An associative array with options for at least
   * 'filename'. 'tablePrefix' is optional.
   * @throws DatabaseConnectionFailedException if connection fails
   */
  public function __construct($options = array()) {
    $this->setTypeAdapter(new  SqliteTypeAdapter($this));
    $this->tablePrefix = 'tbl_';
    if (isset($options['tablePrefix']) and $options['tablePrefix'] != '') {
      $this->tablePrefix = $options['tablePrefix'];
    }
    try {
      $this->handle = new SQLite3($options['filename']);
      $this->initTables($this->rawQuery('SELECT name FROM sqlite_master WHERE type = "table"'));
    }
    catch (DatabaseQueryFailedException $exception) {
      throw new DatabaseConnectionFailedException($exception->getMessage());
    }
    catch (Exception $exception) {
      throw new DatabaseConnectionFailedException(
        tr('SQLite database does not exist and could not be created: %1',
          $options['filename']));
    }
  }

  public function close() {
    $this->handle->close();
  }

  public function quoteString($string) {
    return '"' . $this->handle->escapeString($string) . '"';
  }

  public function rawQuery($sql) {
    Logger::query($sql);
    $result = $this->handle
      ->query($sql);
    if (!$result) {
      throw new DatabaseQueryFailedException($this->handle
        ->lastErrorMsg());
    }
    if (preg_match('/^\\s*(pragma|select|show|explain|describe) /i', $sql)) {
      return new Sqlite3ResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return $this->handle
        ->lastInsertRowID();
    }
    else {
      return $this->handle
        ->changes();
    }
  }
}
