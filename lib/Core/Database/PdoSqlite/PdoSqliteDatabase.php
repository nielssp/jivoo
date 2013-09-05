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

  /**
   * Convert a schema type to an SQLite type
   * @param string $type Schema type name
   * @param string $length Length
   * @param bool|null $unsigned Unsigned if applicable
   * @TODO This is the same in PdoSqlite and Sqlite3... Move to a single
   * location!
   * @return string SQLite type
   */
  public function fromSchematype($type, $length = null, $unsigned = null) {
    switch ($type) {
      case 'string':
        $type = 'TEXT';
        if (!isset($length))
          $length = 255;
        break;
      case 'boolean':
        $type = 'INTEGER';
        $length = 1;
        break;
      case 'integer':
        $type = 'INTEGER';
        $length = null;
        break;
      case 'binary':
        $type = 'BLOB';
        break;
      case 'float':
        $type = 'REAL';
        break;
      default:
        $type = 'TEXT';
        break;
    }
    if (isset($length)) {
      $type .= '(' . $length . ')';
    }
    return $type;
  }

  /**
   * Convert an SQLite type to a Schema type
   * @param string $type SQLite type
   * @return array A 2-tuple of type name and length
   */
  public function toSchemaType($type) {
    $length = null;
    if (strpos($type, '(') !== false) {
      list($type, $right) = explode('(', $type);
      list($length) = explode(')', $right);
      $length = (int) $length;
    }
    else if (strpos($type, 'integer') !== false) {
      if (isset($length) AND $length == 1) {
        $type = 'boolean';
      }
      else {
        $type = 'integer';
      }
    }
    else if (strpos($type, 'blob') !== false) {
      $type = 'binary';
    }
    else if (strpos($type, 'real') !== false) {
      $type = 'float';
    }
    else {
      $type = 'text';
    }
    if ($type != 'string') {
      $length = null;
    }
    return array($type, $length);
  }

  public function getSchema($table) {
    $schema = new Schema($table);
    $result = $this->rawQuery(
        'PRAGMA table_info(' . $this->tableName($table) . ')');
    $primaryKey = array();
    while ($row = $result->fetchAssoc()) {
      $info = array();
      $column = $row['name'];
      $type = $this->toSchemaType($row['type']);
      $info['type'] = $type[0];
      if (isset($type[1])) {
        $info['length'] = $type[1];
      }
      if (isset($row['pk'])) {
        if ($row['pk'] == '1') {
          $primaryKey[] = $column;
        }
      }
      if (isset($row['dflt_value'])) {
        $info['default'] = $row['dflt_value'];
      }
      if (isset($row['notnull'])) {
        $info['null'] = $row['notnull'] == '0';
      }
      $schema->addColumn($column, $info);
    }
    if (!empty($primaryKey)) {
      $schema->setPrimaryKey($primaryKey);
    }
    $result = $this->rawQuery(
        'PRAGMA index_list(' . $this->tableName($table) . ')');
    while ($row = $result->fetchAssoc()) {
      $index = $row['name'];
      $unique = $row['unique'] == 1;
      $columnResult = $this->rawQuery('PRAGMA index_info(' . $index . ')');
      $columns = array();
      while ($row = $columnResult->fetchAssoc()) {
        $columns[] = $row['name'];
      }
      if ($unique) {
        $schema->addUnique($index, $columns);
      }
      else {
        $schema->addIndex($index, $columns);
      }
    }
    return $schema;
  }

  public function tableExists($table) {
    $result = $this->rawQuery(
        'PRAGMA table_info(' . $this->tableName($table) . ')');
    return $result->hasRows();
  }

  public function createTable(Schema $schema) {
    $sql = 'CREATE TABLE ' . $this->tableName($schema->getName()) . '(';
    $columns = $schema->getColumns();
    $first = true;
    foreach ($columns as $column) {
      $options = $schema->$column;
      if (!$first) {
        $sql .= ', ';
      }
      else {
        $first = false;
      }
      $sql .= $column;
      $sql .= ' ' . $this->fromSchemaType($options['type'], $options['length']);
//       if (isset($options['key']) AND $options['key'] == 'primary'
//         AND (!isset($schema->indexes['PRIMARY'])
//           OR isset($options['autoIncrement']))) {
      if ($schema->isPrimaryKey($column)) {
        $sql .= ' PRIMARY KEY';
      }
      if (isset($options['autoIncrement']) AND $options['autoIncrement']) {
        $sql .= ' AUTOINCREMENT';
      }
      if (!$options['null']) {
        $sql .= ' NOT';
      }
      $sql .= ' NULL';
      if (isset($options['default'])) {
        $sql .= $this->escapeQuery(' DEFAULT ?', $options['default']);
      }
    }
    $sql .= ')';
    $this->rawQuery($sql);
    foreach ($schema->getIndexes() as $index => $options) {
      if ($index == 'PRIMARY') {
        continue;
      }
      $sql = 'CREATE';
      if ($options['unique']) {
        $sql .= ' UNIQUE';
      }
      $sql .= ' INDEX ';
      $sql .= $this->tableName($schema->getName()) . '_' . $index;
      $sql .= ' ON ' . $this->tableName($schema->getName());
      $sql .= ' (';
      $sql .= implode(', ', $options['columns']) . ')';
      $this->rawQuery($sql);
    }
  }

  public function dropTable($table) {
    $sql = 'DROP TABLE ' . $this->tableName($table);
    $this->rawQuery($sql);
  }

  public function addColumn($table, $column, $options = array()) {
    $sql = 'ALTER TABLE ' . $this->tableName($table) . ' ADD ' . $column;
    $sql .= ' ' . $this->fromSchemaType($options['type'], $options['length']);
    if (!$options['null']) {
      $sql .= ' NOT';
    }
    $sql .= ' NULL';
    if (isset($options['default'])) {
      $sql .= $this->escapeQuery(' DEFAULT ?', $options['default']);
    }
    $this->rawQuery($sql);
  }

  public function deleteColumn($table, $column) {
    // ALTER TABLE  `posts` DROP  `testing`
    $sql = 'ALTER TABLE ' . $this->tableName($table) . ' DROP ' . $column;
    $this->rawQuery($sql);
  }

  public function alterColumn($table, $column, $options = array()) {
    // UNSUPPORTED
  }

  public function createIndex($table, $index, $options = array()) {
    // UNSUPPORTED
  }

  public function deleteIndex($table, $index) {
    // UNSUPPORTED
  }

  public function alterIndex($table, $index, $options = array()) {
    // UNSUPPORTED
  }
}
