<?php
// Database
// Name              : MySQL
// Dependencies      : php;mysql database>3.6 ext;fancybox<=2.5
// PHPVersion        : 5.2.0
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


  public function fromSchematype($type, $length = NULL) {
    switch ($type) {
      case 'string':
        $type = 'varchar';
        if (!isset($length)) $length = 255;
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
    $length = NULL;
    if (strpos($type, '(') !== FALSE) {
      list($type, $right) = explode('(', $type);
      list($length) = explode(')', $right);
      $length = (int)$length;
    }
    if (strpos($type, 'char') !== FALSE) {
      $type = 'string';
    }
    else if (strpos($type, 'int') !== FALSE) {
      $type = 'integer';
    }
    else if (strpos($type, 'blob') !== FALSE OR $type === 'binary') {
      $type = 'binary';
    }
    else if (strpos($type, 'float') !== FALSE OR strpos($type, 'double') !== FALSE
      OR strpos($type, 'decimal') !== FALSE) {
      $type = 'float';
    }
    else {
      $type = 'text';
    }
    return array($type, $length);
  }

  public function getSchema($table) {
    if (file_exists(p(APP . 'schemas/' . $table . 'Schema.php'))) {
      include(p(APP . 'schemas/' . $table . 'Schema.php'));
      $className = $table . 'Schema';
      return new $className();
    }
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
        if (strpos($row['Extra'], 'auto_increment') !== FALSE) {
          $info['autoIncrement'] = TRUE;
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
      $unique = $row['Non_unique'] == 0 ? TRUE : FALSE;
      $schema->addIndex($index, $column, $unique);
    }
    return $schema;
  }

  public function escapeString($string) {
    return mysql_real_escape_string($string);
  }

  public function tableExists($table) {
    $result = $this->rawQuery('SHOW TABLES LIKE "' . $this->tableName($table) . '"');
    return $result->count() >= 1;
  }

  public function migrate(IMigration $migration) {
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
}
