<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\IMigrationTypeAdapter;

/**
 * Type and migration adapter for SQLite database drivers.
 * @todo Reimplement and document.
 */
class SqliteTypeAdapter implements IMigrationTypeAdapter {

  private $db;

  public function __construct(SqlDatabase $db) {
    $this->db = $db;
  }

  public function encode(DataType $type, $value) {
    $value = $type->convert($value);
    switch ($type->type) {
      case DataType::BOOLEAN:
        return $value ? 1 : 0;
      case DataType::INTEGER:
      case DataType::DATETIME:
      case DataType::DATE:
      case DataType::FLOAT:
        return $value;
      case DataType::STRING:
      case DataType::TEXT:
      case DataType::BINARY:
      case DataType::ENUM:
        return $this->db->quoteString($value);
    }
  }

  public function decode(DataType $type, $value) {
    if (!isset($value))
      return null;
    switch ($type->type) {
      case DataType::BOOLEAN:
        return $value != 0;
      case DataType::INTEGER:
      case DataType::DATE:
      case DataType::DATETIME:
        return intval($value);
      case DataType::FLOAT:
        return floatval($value);
      case DataType::TEXT:
      case DataType::BINARY:
      case DataType::STRING:
      case DataType::ENUM:
        return strval($value);
    }
  }

  /**
   * Convert a schema type to an SQLite type
   * @param DataType $type
   * @return string SQLite type
   */
  public function convertType(DataType $type, $isPrimaryKey = false) {
    $primaryKey = '';
    if ($isPrimaryKey)
      $primaryKey = ' PRIMARY KEY';
    switch ($type->type) {
      case DataType::INTEGER:
        if ($type->size == DataType::BIG)
          $column = 'INTEGER(8)';
        else if ($type->size == DataType::SMALL)
          $column = 'INTEGER(2)';
        else if ($type->size == DataType::TINY)
          $column = 'INTEGER(1)';
        else
          $column = 'INTEGER';
        if ($isPrimaryKey and $type->autoIncrement)
          $primaryKey .= ' AUTOINCREMENT';
        break;
      case DataType::FLOAT:
        $column = 'REAL';
        break;
      case DataType::STRING:
        $column = 'TEXT(' . $type->length . ')';
        break;
      case DataType::BOOLEAN:
        $column = 'INTEGER(1)';
        break;
      case DataType::BINARY:
        $column = 'BLOB';
        break;
      case DataType::DATE:
        $column = 'INTEGER';
        break;
      case DataType::DATETIME:
        $column = 'INTEGER';
        break;
      case DataType::TEXT:
      case DataType::ENUM:
      default:
        $column = 'TEXT';
        break;
    }
    $column .= $primaryKey;
    if ($type->notNull)
      $column .= ' NOT';
    $column .= ' NULL';
    if (isset($type->default))
      $column .= $this->db->escapeQuery(' DEFAULT ?', $type->default);
    return $column;
  }

  /**
   * Convert a MySQL type to a DataType
   * @param string $type MySQL type
   * @return array A 3-tuple of type name, length and unsigned
   */
  public function checkType($row, DataType $type) {
    preg_match('/ *([^ (]+) *(\(([0-9]+)\))? */i', $row['type'], $matches);
    $actualType = strtolower($matches[1]);
    $length = isset($matches[3]) ? $matches[3] : 0;
    $null = (isset($row['notnull']) and $row['notnull'] != '1');
    if ($null != $type->null)
      return false;
    $default = null;
    if (isset($row['dflt_value']))
      $default = stripslashes(preg_replace('/^\'|\'$/', '', $row['dflt_value']));
    if ($default != $type->default)
      return false;
    switch ($type->type) {
      case DataType::INTEGER:
        if ($actualType != 'integer')
          return false;
//         if ($type->size == DataType::BIG and $length != '8')
//           return false;
//         else if ($type->size == DataType::SMALL and $length != '2')
//           return false;
//         else if ($type->size == DataType::TINY and $length != '1')
//           return false;
//         else if ($length != '4')
//           return false;
        break;
      case DataType::FLOAT:
        if ($actualType != 'real')
          return false;
        break;
      case DataType::STRING:
        if ($actualType != 'text')
          return false;
        if ($type->length != $length)
          return false;
        break;
      case DataType::BOOLEAN:
        if ($actualType != 'integer')
          return false;
        break;
      case DataType::BINARY:
        if ($actualType != 'blob')
          return false;
        break;
      case DataType::DATE:
        if ($actualType != 'integer')
          return false;
        break;
      case DataType::DATETIME:
        if ($actualType != 'integer')
          return false;
        break;
      case DataType::TEXT:
      default:
        if ($actualType != 'text')
          return false;
        break;
    }
    return true;
  }

  public function checkSchema($table, ISchema $schema) {
    $result = $this->db->rawQuery('PRAGMA table_info("' . $this->db->tableName($table) . '")');
    $columns = array();
    $primaryKey = array();
    while ($row = $result->fetchAssoc()) {
      $column = $row['name'];
      if (isset($row['pk']) and $row['pk'] == '1')
        $primaryKey[] = $column;
      if (isset($schema->$column))
        $columns[$column] = $this->checkType($row, $schema->$column) ? 'ok' : 'alter';
      else
        $columns[$column] = 'delete';
    }
    foreach ($schema->getFields() as $field) {
      if (!isset($columns[$field]))
        $columns[$field] = 'add';
    }
    $result = $this->db->rawQuery('PRAGMA index_list("' . $this->db->tableName($table) . '")');
    $actualIndexes = array();
    $actualIndexes['PRIMARY'] = array(
      'columns' => $primaryKey,
      'unique' => true
    );
    while ($row = $result->fetchAssoc()) {
      $index = $row['name'];
      $unique = $row['unique'] == 1;
      $name = preg_replace(
        '/^' . preg_quote($this->db->tableName($table) . '_', '/') . '/',
        '', $index, 1, $count
      );
      if ($count == 0)
        continue;
      $columnResult = $this->db->rawQuery('PRAGMA index_info("' . $index . '")');
      $indexFields = array();
      while ($row = $columnResult->fetchAssoc()) {
        $indexFields[] = $row['name'];
      }
      $actualIndexes[$name] = array(
        'columns' => $indexFields,
        'unique' => $unique
      );
    }
    $expectedIndexes = $schema->getIndexes();
    $allIndexes = array_unique(array_merge(
      array_keys($actualIndexes), array_keys($expectedIndexes)
    ));
    $indexes = array();
    foreach ($allIndexes as $index) {
      if (!isset($actualIndexes[$index]))
        $indexes[$index] = 'add';
      else if (!isset($expectedIndexes[$index]))
        $indexes[$index] = 'delete';
      else if ($actualIndexes[$index] != $expectedIndexes[$index])
        $indexes[$index] = 'alter';
      else
        $indexes[$index] = 'ok';
    }
    return array(
      'columns' => $columns,
      'indexes' => $indexes
    );
  }

  public function tableExists($table) {
    $result = $this->db->rawQuery(
      'PRAGMA table_info("' . $this->db->tableName($table) . '")');
    return $result->hasRows();
  }

  public function createTable(Schema $schema) {
    $sql = 'CREATE TABLE "' . $this->db->tableName($schema->getName()) . '" (';
    $columns = $schema->getFields();
    $first = true;
    $primaryKey = $schema->getPrimaryKey();
    $singlePrimary = count($primaryKey) ==  1;
    foreach ($columns as $column) {
      $type = $schema->$column;
      if (!$first) {
        $sql .= ', ';
      }
      else {
        $first = false;
      }
      $sql .= $column;
      $sql .= ' ' . $this->convertType($type, $singlePrimary and $primaryKey[0] == $column);
    }
    if (!$singlePrimary) {
      $sql .= ', PRIMARY KEY (' . implode(', ', $schema->getPrimaryKey()) . ')';
    }
    $sql .= ')';
    $this->db->rawQuery($sql);
    foreach ($schema->getIndexes() as $index => $options) {
      if ($index == 'PRIMARY') {
        continue;
      }
      $sql = 'CREATE';
      if ($options['unique']) {
        $sql .= ' UNIQUE';
      }
      $sql .= ' INDEX "';
      $sql .= $this->db->tableName($schema->getName()) . '_' . $index;
      $sql .= '" ON "' . $this->db->tableName($schema->getName());
      $sql .= '" (';
      $sql .= implode(', ', $options['columns']) . ')';
      $this->db->rawQuery($sql);
    }
  }

  public function dropTable($table) {
    $sql = 'DROP TABLE "' . $this->db->tableName($table) . '"';
    $this->db->rawQuery($sql);
  }

  public function addColumn($table, $column, DataType $type) {
    $sql = 'ALTER TABLE "' . $this->db->tableName($table) . '" ADD ' . $column;
    $sql .= ' ' . $this->convertType($type);
    $this->db->rawQuery($sql);
  }

  public function deleteColumn($table, $column) {
    throw new \Exception(tr('Not implemented'));
  }

  public function alterColumn($table, $column, DataType $type) {
    throw new \Exception(tr('Not implemented'));
  }

  public function renameColumn($table, $column, $newName) {
    throw new \Exception(tr('Not implemented'));
  }

  public function createIndex($table, $index, $options = array()) {
    $sql = 'CREATE';
    if ($options['unique']) {
      $sql .= ' UNIQUE';
    }
    $sql .= ' INDEX "';
    $sql .= $this->db->tableName($table) . '_' . $index;
    $sql .= '" ON "' . $this->db->tableName($table);
    $sql .= '" (';
    $sql .= implode(', ', $options['columns']) . ')';
    $this->db->rawQuery($sql);
  }

  public function deleteIndex($table, $index) {
    $sql = 'DROP INDEX "';
    $sql .= $this->db->tableName($table) . '_' . $index . '"';
    $this->db->rawQuery($sql);
  }

  public function alterIndex($table, $index, $options = array()) {
    $this->deleteIndex($table, $index);
    $this->createIndex($table, $index, $options);
  }
}
