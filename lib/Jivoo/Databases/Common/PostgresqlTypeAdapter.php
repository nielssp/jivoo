<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\IMigrationTypeAdapter;
use Jivoo\Models\DataType;
use Jivoo\Databases\Schema;
use Jivoo\Core\Utilities;
use Jivoo\Core\Json;

/**
 * Type adapter for PostgreSQL database drivers.
 */
class PostgresqlTypeAdapter implements IMigrationTypeAdapter {
  /**
   * @var SqlDatabase Database
   */
  private $db;

  /**
   * Construct type adapter.
   * @param SqlDatabase $db Database.
   */
  public function __construct(SqlDatabase $db) {
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  public function encode(DataType $type, $value) {
    $value = $type->convert($value);
    if (!isset($value)) {
      if ($type->isInteger() and $type->autoIncrement)
        return 'DEFAULT';
      return 'NULL';
    }
    switch ($type->type) {
      case DataType::INTEGER:
        return intval($value);
      case DataType::FLOAT:
        return floatval($value);
      case DataType::BOOLEAN:
        return $value ? 'TRUE' : 'FALSE';
      case DataType::DATE:
        return $this->db->quoteString(gmdate('Y-m-d', $value));
      case DataType::DATETIME:
        return $this->db->quoteString(gmdate('Y-m-d H:i:s', $value));
      case DataType::STRING:
      case DataType::TEXT:
      case DataType::BINARY:
      case DataType::ENUM:
        return $this->db->quoteString($value);
      case DataType::OBJECT:
        return $this->db->quoteString(Json::encode($value));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function decode(DataType $type, $value) {
    if (!isset($value))
      return null;
    switch ($type->type) {
      case DataType::BOOLEAN:
        return $value != 0;
      case DataType::DATE:
      case DataType::DATETIME:
        return strtotime($value . ' UTC');
      case DataType::INTEGER:
        return intval($value);
      case DataType::FLOAT:
        return floatval($value);
      case DataType::STRING:
      case DataType::TEXT:
      case DataType::BINARY:
      case DataType::ENUM:
        return strval($value);
      case DataType::OBJECT:
        return Json::decode($value);
    }
  }

  /**
   * Convert a schema type to a PostgreSQL type.
   * @param DataType $type Type.
   * @return string PostgreSQL type.
   */
  private function fromDataType(DataType $type) {
    $autoIncrement = '';
    switch ($type->type) {
      case DataType::INTEGER:
        $column = '';
        if ($type->size == DataType::BIG)
          $column = 'big';
        else if ($type->size == DataType::SMALL)
          $column = 'small';
        else if ($type->size == DataType::TINY)
          $column = 'small';
        if ($type->autoIncrement)
          $column .='serial';
        else
          $column .= 'int';
        break;
      case DataType::FLOAT:
        $column = 'double';
        break;
      case DataType::STRING:
        $column = 'varchar(' . $type->length . ')';
        break;
      case DataType::BOOLEAN:
        $column = 'boolean';
        break;
      case DataType::BINARY:
        // TODO: use bytea
        $column = 'text';
        break;
      case DataType::DATE:
        $column = 'date';
        break;
      case DataType::DATETIME:
        $column = 'timestamp';
        break;
      case DataType::ENUM:
        // TODO: add support for enums using CREATE TYPE
        $column = 'varchar(255)';
//         $column = "ENUM('" . implode("','", $type->values) . "')";
        break; 
      case DataType::TEXT:
      case DataType::OBJECT:
      default:
        $column = 'text';
        break;
    }
    if ($type->notNull)
      $column .= ' NOT NULL';
    if (isset($type->default))
      $column .= ' DEFAULT ' . $this->encode($type, $type->default);
    return $column . $autoIncrement;
  }
  
  /**
   * Convert output of SHOW COLUMN to DataType.
   * @param array $row Row result.
   * @throws \Exception If type unsupported.
   * @return DataType The type.
   */
  private function toDataType($row) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $null = (isset($row['Null']) and $row['Null'] != 'NO');
    $default = null;
    if (isset($row['Default']))
      $default = $row['Default'];
    
    if (preg_match('/enum\((.+)\)/i', $row['Type'], $matches) === 1) {
      preg_match_all('/\'([^\']+)\'/', $matches[1], $matches);
      $values = $matches[1];
      return DataType::enum($values, $null, $default);
    }
    preg_match('/ *([^ (]+) *(\(([0-9]+)\))? *(unsigned)? *?/i', $row['Type'], $matches);
    $actualType = strtolower($matches[1]);
    $length = isset($matches[3]) ? intval($matches[3]) : 0;
    $intFlags = 0;
    if (isset($matches[4]))
      $intFlags |= DataType::UNSIGNED;
    if (strpos($row['Extra'], 'auto_increment') !== false)
      $intFlags |= DataType::AUTO_INCREMENT;
    switch ($actualType) {
      case 'bigint':
        $intFlags |= DataType::BIG;
        return DataType::integer($intFlags, $null, intval($default));
      case 'smallint':
        $intFlags |= DataType::SMALL;
        return DataType::integer($intFlags, $null, intval($default));
      case 'tinyint':
        $intFlags |= DataType::TINY;
        return DataType::integer($intFlags, $null, intval($default));
      case 'int':
        return DataType::integer($intFlags, $null, intval($default));
      case 'double':
        return DataType::float($null, floatval($default));
      case 'varchar':
        return DataType::string($length, $null, $default);
      case 'blob':
        return DataType::binary($null, $default);
      case 'date':
        return DataType::date($null, strtotime($default . ' UTC'));
      case 'datetime':
        return DataType::dateTime($null, strtotime($default . ' UTC'));
      case 'text':
        return DataType::text($null, $default);
    }
    throw new \Exception(tr(
      'Unsupported PostgreSQL type for column: %1', $row['Field']
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getTableSchema($table) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $result = $this->db->rawQuery('SHOW COLUMNS FROM `' . $this->db->tableName($table) . '`');
    $schema = new Schema($table);
    while ($row = $result->fetchAssoc()) {
      $column = $row['Field'];
      $schema->addField($column, $this->toDataType($row));
    }
    $result = $this->db->rawQuery('SHOW INDEX FROM `' . $this->db->tableName($table) . '`');
    $indexes = array();
    while ($row = $result->fetchAssoc()) {
      $index = $row['Key_name'];
      $column = $row['Column_name'];
      $unique = $row['Non_unique'] == 0 ? true : false;
      if (isset($indexes[$index]))
        $indexes[$index]['columns'][] = $column;
      else
        $indexes[$index] = array(
          'columns' => array($column),
          'unique' => $unique
        );
    }
    foreach ($indexes as $name => $index) {
      if ($index['unique'])
        $schema->addUnique($name, $index['columns']);
      else
        $schema->addIndex($name, $index['columns']);
    }
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function tableExists($table) {
    $result = $this->db->rawQuery(
      // TODO: custom schemaname?
      "SELECT 1 FROM pg_catalog.pg_tables WHERE schemaname = 'public' AND tablename = '"
      . $this->db->tableName($table) . "'"
    );
    return $result->hasRows();
  }

  /**
   * {@inheritdoc}
   */
  public function getTables() {
    $prefix = $this->db->tableName('');
    $prefixLength = strlen($prefix);
    $result = $this->db->rawQuery("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
    $tables = array();
    while ($row = $result->fetchRow()) {
      $name = $row[0];
      if (substr($name, 0, $prefixLength) == $prefix) {
        $name = substr($name, $prefixLength);
        $tables[] = Utilities::underscoresToCamelCase($name);
      }
    }
    return $tables;
  }

  /**
   * {@inheritdoc}
   */
  public function createTable(Schema $schema) {
    $sql = 'CREATE TABLE "' . $this->db->tableName($schema->getName()) . '" (';
    $columns = $schema->getFields();
    $first = true;
    foreach ($columns as $column) {
      $type = $schema->$column;
      if (!$first) {
        $sql .= ', ';
      }
      else {
        $first = false;
      }
      $sql .= $column;
      $sql .= ' ' . $this->fromDataType($type);
    }
    $createIndex = array();
    foreach ($schema->getIndexes() as $index => $options) {
      if ($index == 'PRIMARY') {
        $sql .= ', PRIMARY KEY (';
      }
      else if ($options['unique']) {
        $sql .= ', UNIQUE (';
      }
      else {
        $createIndex[$index] = $options['columns'];
        continue;
      }
      $sql .= implode(', ', $options['columns']) . ')';
    }
    $sql .= ')';
    $this->db->rawQuery($sql);
    foreach ($createIndex as $index => $columns) {
      $sql = 'CREATE INDEX ON "' . $this->db->tableName($schema->getName()) . '" (';
      $sql .= implode(', ', $columns) . ')';
      $this->db->rawQuery($sql);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function renameTable($table, $newName) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'RENAME TABLE `' . $this->db->tableName($table) . '` TO `';
    $sql .= $this->db->tableName($newName) . '`';
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function dropTable($table) {
    $sql = 'DROP TABLE "' . $this->db->tableName($table) . '"';
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function addColumn($table, $column, DataType $type) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` ADD ' . $column;
    $sql .= ' ' . $this->fromDataType($type);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteColumn($table, $column) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` DROP ' . $column;
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function alterColumn($table, $column, DataType $type) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` CHANGE ' . $column
        . ' ' . $column;
    $sql .= ' ' . $this->fromDataType($type);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function renameColumn($table, $column, $newName) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $type = $this->db->$table->getSchema()->$column;
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '` CHANGE ' . $column
        . ' ' . $newName;
    $sql .= ' ' . $this->fromDataType($type);
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function createIndex($table, $index, $options = array()) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '`';
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
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIndex($table, $index) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '`';
    if ($index == 'PRIMARY') {
      $sql .= ' DROP PRIMARY KEY';
    }
    else {
      $sql .= ' DROP INDEX ' . $index;
    }
    $this->db->rawQuery($sql);
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndex($table, $index, $options = array()) {
    // TODO: implement
    throw new \Exception('not yet implemented');
    $sql = 'ALTER TABLE `' . $this->db->tableName($table) . '`';
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
    $this->db->rawQuery($sql);
  }
}
