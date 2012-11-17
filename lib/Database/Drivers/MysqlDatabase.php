<?php
// Database
// Name              : MySQL
// Dependencies      : php;mysql
// Required          : server username database
// Optional          : password tablePrefix

class MysqlDatabase extends SqlDatabase {
  private $handle;

  public function __construct($options = array()) {
    if (isset($options['tablePrefix'])) {
      $this->tablePrefix = $options['tablePrefix'];
    }
    $this->handle = mysql_connect($options['server'], $options['username'], $options['password'], true);
    if (!$this->handle) {
      throw new DatabaseConnectionFailedException(mysql_error());
    }
    if (!mysql_select_db($options['database'], $this->handle)) {
      throw new DatabaseSelectFailedException(mysql_error());
    }
  }

  public function close() {
    mysql_close($this->handle);
  }


  public function fromSchematype($type, $length = null) {
    switch ($type) {
      case 'string':
        $type = 'varchar';
        if (!isset($length)) $length = 255;
        break;
      case 'boolean':
        $type = 'bool';
        break;
      case 'integer':
        $type = 'int';
        break;
      case 'binary':
        $type = 'blob';
        break;
      case 'float':
        $type = 'double';
        break;
      default:
        $type = 'text';
        break;
    }
    if (isset($length)) {
      $type .= '(' . $length . ')';
    }
    return $type;
  }

  public function toSchemaType($type) {
    $length = null;
    if (strpos($type, '(') !== false) {
      list($type, $right) = explode('(', $type);
      list($length) = explode(')', $right);
      $length = (int)$length;
    }
    if (strpos($type, 'char') !== false) {
      $type = 'string';
    }
    else if (strpos($type, 'tinyint') !== false OR strpos($type, 'bool') !== false) {
      $type = 'boolean';
    }
    else if (strpos($type, 'int') !== false) {
      $type = 'integer';
    }
    else if (strpos($type, 'blob') !== false OR $type === 'binary') {
      $type = 'binary';
    }
    else if (strpos($type, 'float') !== false OR strpos($type, 'double') !== false
      OR strpos($type, 'decimal') !== false) {
      $type = 'float';
    }
    else {
      $type = 'text';
    }
    return array($type, $length);
  }

  public function getSchema($table) {
    $schema = new Schema($table);
    $result = $this->rawQuery('SHOW COLUMNS FROM ' . $this->tableName($table));
    while ($row = $result->fetchAssoc()) {
      $info = array();
      $column = $row['Field'];
      $type = $this->toSchemaType($row['Type']);
      $info['type'] = $type[0];
      if (isset($type[1])) {
        $info['length'] = $type[1];
      }
      if (isset($row['Key'])) {
        if ($row['Key'] == 'PRI') {
          $info['key'] = 'primary';
        }
        else if ($row['Key'] == 'UNI') {
          $info['key'] = 'unique';
        }
        else if ($row['Key'] == 'MUL') {
          $info['key'] = 'index';
        }
      }
      if (isset($row['Extra'])) {
        if (strpos($row['Extra'], 'auto_increment') !== false) {
          $info['autoIncrement'] = true;
        }
      }
      if (isset($row['Default'])) {
        $info['default'] = $row['Default'];
      }
      if (isset($row['Null'])) {
        $info['null'] = $row['Null'] != 'NO';
      }
      $schema->addColumn($column, $info);
    }
    $result = $this->rawQuery('SHOW INDEX FROM ' . $this->tableName($table));
    while ($row = $result->fetchAssoc()) {
      $index = $row['Key_name'];
      $column = $row['Column_name'];
      $unique = $row['Non_unique'] == 0 ? true : false;
      $schema->addIndex($index, $column, $unique);
    }
    return $schema;
  }

  public function quoteString($string) {
    return '"' . mysql_real_escape_string($string) . '"';
  }

  public function tableExists($table) {
    $result = $this->rawQuery('SHOW TABLES LIKE "' . $this->tableName($table) . '"');
    return $result->hasRows();
  }

  public function rawQuery($sql) {
    $result = mysql_query($sql, $this->handle);
    if (!$result) {
      throw new DatabaseQueryFailedException(mysql_error());
    }
    if (preg_match('/^\\s*(select|show|explain|describe) /i', $sql)) {
      return new MysqlResultSet($result);
    }
    else if (preg_match('/^\\s*(insert|replace) /i', $sql)) {
      return mysql_insert_id($this->handle);
    }
    else {
      return mysql_affected_rows($this->handle);
    }
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
      if (!$options['null']) {
        $sql .= ' NOT';
      }
      $sql .= ' NULL';
      if (isset($options['default'])) {
        $sql .= $this->escapeQuery(' DEFAULT ?', $options['default']);
      }
      if (isset($options['autoIncrement'])) {
        $sql .= ' AUTO_INCREMENT';
      }
    }
    foreach ($schema->indexes as $index => $options) {
      $sql .= ', ';
      if ($index == 'PRIMARY') {
        $sql .= 'PRIMARY KEY (';
      }
      else if ($options['unique']) {
        $sql .= 'UNIQUE (';
      }
      else {
        $sql .= 'INDEX (';
      }
      $sql .= implode(', ', $options['columns']) . ')';
    }
    $sql .= ') CHARACTER SET utf8';
    $this->rawQuery($sql);
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
    if (isset($options['autoIncrement'])) {
      $sql .= ' AUTO_INCREMENT';
    }
    $this->rawQuery($sql);
  }

  public function deleteColumn($table, $column) {
    // ALTER TABLE  `posts` DROP  `testing`
    $sql = 'ALTER TABLE ' . $this->tableName($table) . ' DROP ' . $column;
    $this->rawQuery($sql);
  }

  public function alterColumn($table, $column, $options = array()) {
    // ALTER TABLE  `posts` CHANGE  `testing`  `testing` INT( 12 ) NOT null
    $sql = 'ALTER TABLE ' . $this->tableName($table) . ' CHANGE ' . $column . ' ' . $column;
    $sql .= ' ' . $this->fromSchemaType($options['type'], $options['length']);
    if (!$options['null']) {
      $sql .= ' NOT';
    }
    $sql .= ' NULL';
    if (isset($options['default'])) {
      $sql .= $this->escapeQuery(' DEFAULT ?', $options['default']);
    }
    if (isset($options['autoIncrement'])) {
      $sql .= ' AUTO_INCREMENT';
    }
    if (isset($options['key'])) {
      if ($options['key'] == 'primary') {
        $sql .= ' PRIMARY';
      }
    }
    $this->rawQuery($sql);
  }

  public function createIndex($table, $index, $options = array()) {
    $sql = 'ALTER TABLE ' . $this->tableName($table);
    if ($index == 'PRIMARY') {
      $sql .= ' ADD PRIMARY KEY';
    }
    else if ($options['unique']) {
      $sql .= ' ADD UNIQUE ' . $index;
    }
    else {
      $sql .= ' ADD INDEX ' . $index;
    }
    $sql .= ' (';
    $sql .= implode(', ', $options['columns']);
    $sql .= ')';
    $this->rawQuery($sql);
  }

  public function deleteIndex($table, $index) {
    // ALTER TABLE  `posts` DROP INDEX  `name_2`
    $sql = 'ALTER TABLE ' . $this->tableName($table);
    if ($index == 'PRIMARY') {
      $sql .= ' DROP PRIMARY KEY';
    }
    else {
      $sql .= ' DROP INDEX ' . $index;
    }
    $this->rawQuery($sql);
  }

  public function alterIndex($table, $index, $options = array()) {
    $sql = 'ALTER TABLE ' . $this->tableName($table);
    if ($index == 'PRIMARY') {
      $sql .= ' DROP PRIMARY KEY';
    }
    else {
      $sql .= ' DROP INDEX ' . $index;
    }
    $sql .= ', ';
    if ($index == 'PRIMARY') {
      $sql .= ' ADD PRIMARY KEY';
    }
    else if ($options['unique']) {
      $sql .= ' ADD UNIQUE ' . $index;
    }
    else {
      $sql .= ' ADD INDEX ' . $index;
    }
    $sql .= ' (';
    $sql .= implode(', ', $options['columns']);
    $sql .= ')';
    $this->rawQuery($sql);
  }
}
