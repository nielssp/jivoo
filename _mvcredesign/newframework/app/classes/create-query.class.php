<?php

class CreateQuery extends Query {

  private $table;

  private $primaryKey;
  private $indexes = array();

  private $columns = array();

  public static function create($table = NULL) {
    $query = new self();
    $query->table = $table;
    return $query;
  }

  public function setTable($table) {
    $this->table = $table;
    return $this;
  }

  public function addInt($name, $unsigned = FALSE, $autoIncrement = FALSE, $default = NULL, $null = FALSE) {
    if (!isset($this->primaryKey)) {
      $this->primaryKey = $name;
    }
    $this->columns[$name] = array(
      'type' => 'INT',
      'unsigned' => $unsigned,
      'autoIncrement' => $autoIncrement,
      'default' => $default,
      'null' => $null
    );
    return $this;
  }

  public function addReal($name, $default = NULL, $null = FALSE) {
    if (!isset($this->primaryKey)) {
      $this->primaryKey = $name;
    }
    $this->columns[$name] = array(
      'type' => 'REAL',
      'default' => $default,
      'null' => $null
    );
    return $this;
  }

  public function addVarchar($name, $length, $default = NULL, $null = FALSE) {
    if (!isset($this->primaryKey)) {
      $this->primaryKey = $name;
    }
    $this->columns[$name] = array(
      'type' => 'VARCHAR',
      'length' => $length,
      'default' => $default,
      'null' => $null
    );
    return $this;
  }

  public function addText($name, $length = NULL, $default = NULL, $null = FALSE) {
    if (!isset($this->primaryKey)) {
      $this->primaryKey = $name;
    }
    $this->columns[$name] = array(
      'type' => 'TEXT',
      'length' => $length,
      'default' => $default,
      'null' => $null
    );
    return $this;
  }

  public function addBlob($name, $length = NULL, $default = NULL, $null = FALSE) {
    if (!isset($this->primaryKey)) {
      $this->primaryKey = $name;
    }
    $this->columns[$name] = array(
      'type' => 'BLOB',
      'length' => $length,
      'default' => $default,
      'null' => $null
    );
    return $this;
  }

  public function setPrimaryKey($column) {
    $this->primaryKey = func_get_args();
    return $this;
  }

  public function addIndex($unique = FALSE, $column) {
    $columns = func_get_args();
    array_shift($columns);
    $this->indexes[] = array(
      'unique' => $unique,
      'columns' => $columns
    );
    return $this;
  }
/*
  CREATE TABLE `peanutcms-testing`.`posts2` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `date` INT NOT NULL,
  INDEX (`title`),
  UNIQUE (`name`)) ENGINE = InnoDB;

  CREATE TABLE  `peanutcms-testing`.`posts3` (
`int1` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`int2` INT NOT NULL DEFAULT  '2',
`real` REAL NOT NULL DEFAULT  '23',
`text` TEXT NOT NULL ,
`varchar1` VARCHAR( 23 ) NULL DEFAULT NULL ,
`varchar2` VARCHAR( 23 ) NULL DEFAULT  'HElloThere',
INDEX (  `varchar1` ,  `varchar2` ) ,
UNIQUE (
`int2` ,
`real`
)
) ENGINE = INNODB;
  */

  public function toSql(IDatabase $db) {
    $sqlString = 'CREATE TABLE ' . $db->tableName($this->table) . ' (';
    $columnStrings = array();
    $primaryKeys = count($this->primaryKey);
    foreach ($this->columns as $name => $options) {
      $columnStrings[$name] = $name . ' ' . $options['type'];
      if (isset($options['length'])) {
        $columnStrings[$name] .= '(' . $options['length'] . ')';
      }
      if (isset($options['unsigned']) AND $options['unsigned']) {
        $columnStrings[$name] .= ' UNSIGNED';
      }
      if (!$options['null']) {
        $columnStrings[$name] .= ' NOT NULL';
      }
      if (isset($options['default'])) {
        $columnStrings[$name] .= $db->escapeQuery(' DEFAULT ?', $options['default']);
      }
      if (isset($options['autoIncrement']) AND $options['autoIncrement']) {
        $columnStrings[$name] .= ' AUTO_INCREMENT';
      }
      if ($primaryKeys == 1 AND $this->primaryKey[0] == $name) {
        $columnStrings[$name] .= ' PRIMARY KEY';
      }
    }
    $sqlString .= implode(', ', $columnStrings);
    if ($primaryKeys > 1) {
      $sqlString .= ', PRIMARY KEY (' . implode(', ', $this->primaryKey) . ')';
    }
    foreach ($this->indexes as $id => $options) {
      if ($options['unique']) {
        $sqlString .= ', UNIQUE ';
      }
      else {
        $sqlString .= ', INDEX ';
      }
      $sqlString .= '(' . implode(', ', $options['columns']) . ')';
    }
    $sqlString .= ')';
    return $sqlString;
  }

}