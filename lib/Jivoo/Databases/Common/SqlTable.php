<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\Table;
use Jivoo\Core\App;
use Jivoo\Models\ISchema;
use Jivoo\Models\Condition\Condition;
use Jivoo\Models\Selection\ReadSelection;
use Jivoo\Models\Selection\UpdateSelection;
use Jivoo\Models\Selection\DeleteSelection;
use Jivoo\Models\Record;
use Jivoo\Models\Condition\NotCondition;

/**
 * A table in an SQL database.
 */
class SqlTable extends Table {
  /**
   * @var SqlDatabase Owner database.
   */
  private $owner = null;

  /**
   * @var string Table name (without prefix etc.).
   */
  private $name = '';

  /**
   * @var Schema|null Table schema if set.
   */
  private $schema = null;

  /**
   * Construct table.
   * @param App $app Associated application.
   * @param SqlDatabase $database Owner database.
   * @param string $table Table name (without prefix etc.).
   */
  public function __construct(App $app, SqlDatabase $database, $table) {
    $this->owner = $database;
    $this->name = $table;
    $this->schema = $this->owner->getSchema()->getSchema($table);
    parent::__construct($app);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function setSchema(ISchema $schema) {
    $this->schema = $schema;
  }
  
  /**
   * {@inheritdoc}
   */
  public function createExisting($raw = array(), ReadSelection $selection) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $additional = $selection->additionalFields;
    $data = array();
    $virtual = array();
    $subrecords = array();
    foreach ($raw as $field => $value) {
      if (isset($additional[$field])) {
        if (isset($additional[$field]['type']))
          $value = $typeAdapter->decode($additional[$field]['type'], $value);
        if (isset($additional[$field]['record'])) {
          $record = $additional[$field]['record'];
          if (!isset($subrecords[$record])) {
            $subrecords[$record] = array(
              'model' => $additional[$field]['model'],
              'null' => true,
              'data' => array()
            );
          }
          $subrecords[$record]['data'][$additional[$field]['recordField']] = $value;
          if (isset($value))
            $subrecords[$record]['null'] = false;
        }
        else {
          $virtual[$field] = $value;
        }
      }
      else {
        $type = $this->getType($field);
        if (!isset($type))
          throw new \Exception(tr(
            'Schema %1 does not contain field %2', $this->getName(), $field
          ));
        $data[$field] = $typeAdapter->decode($this->getType($field), $value);
      }
    }
    foreach ($subrecords as $field => $record) {
      if ($record['null']) {
        $virtual[$field] = null;
      }
      else {
        $virtual[$field] = Record::createExisting($record['model'], $record['data']);
      }
    }
    return Record::createExisting($this, $data, $virtual);
  }

  /**
   * Convert a condition to SQL.
   * @param Condition $where The condition.
   * @return string SQL subquery.
   */
  protected function conditionToSql(Condition $where) {
    $sqlString = '';
    foreach ($where->clauses as $clause) {
      if ($sqlString != '') {
        $sqlString .= ' ' . $clause['glue'] . ' ';
      }
      if ($clause['clause'] instanceof Condition) {
        if ($clause['clause']->hasClauses()) {
          if ($clause['clause'] instanceof NotCondition) {
            $sqlString .= 'NOT ';
          }
          $sqlString .= '(' . $this->conditionToSql($clause['clause']) . ')';
        }
      }
      else {
        $sqlString .= $this->owner->escapeQuery($clause['clause'], $clause['vars']);
      }
    }
    return $sqlString;
  }
  
  /**
   * For use with array_walk(), will run {@see SqlTable::owner->escapeQuery()} on
   * each column in an array. The input $value should be an associative array
   * as described in the documentation for {@see SelectQuery::$columns}.
   * The resulting $value will be a string.
   * @param array $value Array reference.
   * @param mixed $key Key (not used).
   */
  protected function getColumnList(&$value, $key) {
    $expression = $this->owner->escapeQuery($value['expression'], array());
    if (isset($value['alias'])) {
      $value = $expression . ' AS ' . $value['alias'];
    }
    else {
      $value = $expression;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function countSelection(ReadSelection $selection) {
    if (isset($selection->groupBy)) {
      $result = $this->owner->rawQuery(
        'SELECT COUNT(*) FROM (' . $this->convertReadSelection($selection, '1') . ') AS _selection_count'
      );
      $row = $result->fetchAssoc();
      return $row['COUNT(*)'];
    }
    else {
      $result = $selection->select('COUNT(*)');
      return $result[0]['COUNT(*)'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function readSelection(ReadSelection $selection) {
    return $this->owner->rawQuery($this->convertReadSelection($selection));
  }

  /**
   * Convert a read selection to an SQL query.
   * @param ReadSelection $selection Read selection.
   * @param string|null $projection Projection override.
   * @return string SQL query.
   */
  private function convertReadSelection(ReadSelection $selection, $projection = null) {
    $sqlString = 'SELECT ';
    if (isset($projection)) {
      $sqlString .= $projection;
    }
    else if (!empty($selection->fields)) {
      $fields = $selection->fields;
      array_walk($fields, array($this, 'getColumnList'));
      $sqlString .= implode(', ', $fields);
    }
    else {
      if (isset($selection->alias))
        $sqlString .= $selection->alias . '.*';
      else
        $sqlString .= $this->owner->quoteTableName($this->name) . '.*';
      if (!empty($selection->additionalFields)) {
        $fields = $selection->additionalFields;
        array_walk($fields, array($this, 'getColumnList'));
        $sqlString .= ', ' . implode(', ', $fields);
      }
    }
    $sqlString .= ' FROM ' . $this->owner->quoteTableName($this->name);
    if (isset($selection->alias))
      $sqlString .= ' AS ' . $selection->alias; 
    if (!empty($selection->sources)) {
      foreach ($selection->sources as $source) {
        if (is_string($source['source'])) {
          $table = $source['source'];
        }
        else if ($source['source'] instanceof SqlTable) {
          $table = $source['source']->name;
        }
        else {
          continue;
        }
        $sqlString .= ', ' . $this->owner->quoteTableName($table);
        if (isset($source['alias'])) {
          $sqlString .= ' AS ' . $source['alias'];
        }
      }
    }
    if (!empty($selection->joins)) {
      foreach ($selection->joins as $join) {
        $joinSource = $join['source']->asInstanceOf('Jivoo\Databases\Common\SqlTable');
        if (!isset($joinSource)) {
          throw new \Exception(tr(
            'Unable to join SqlTable with data source of type "%1"',
            get_class($join['source'])
          ));
        }

        if ($joinSource->owner !== $this->owner) {
          throw new \Exception(tr(
            'Unable to join SqlTable with table of different database'
          ));
        }
        $table = $joinSource->name;

        $sqlString .= ' ' . $join['type'] . ' JOIN ' . $this->owner->quoteTableName($table);
        if (isset($join['alias'])) {
          $sqlString .= ' AS ' . $join['alias'];
        }
        if (isset($join['condition']) AND $join['condition']->hasClauses()) {
          $sqlString .= ' ON ' . $this->conditionToSql($join['condition']);
        }
      }
    }
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (isset($selection->groupBy)) {
      $columns = array();
      foreach ($selection->groupBy['columns'] as $column) {
        $columns[] = $this->owner->escapeQuery($column);
      }
      $sqlString .= ' GROUP BY ' . implode(', ', $columns);
      if (isset($selection->groupBy['condition'])
          AND $selection->groupBy['condition']->hasClauses()) {
        $sqlString .= ' HAVING '
          . $this->conditionToSql($selection->groupBy['condition']);
      }
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->owner->escapeQuery($orderBy['column'])
        . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit)) {
      $sqlString .= ' LIMIT ' . $selection->offset . ', ' . $selection->limit;
    }
    return $sqlString;
  }
  
  /**
   * {@inheritdoc}
   */
  public function updateSelection(UpdateSelection $selection) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $sqlString = 'UPDATE ' . $this->owner->quoteTableName($this->name);
    $sets = $selection->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET';
      reset($sets);
      $first = true;
      foreach ($sets as $key => $value) {
        if ($first) {
          $first = false;
        }
        else {
          $sqlString .= ',';
        }
        if (strpos($key, '=') !== false) {
          $sqlString .= ' ' . $this->owner->escapeQuery($key, $value);
        }
        else {
          $sqlString .= ' ' . $key . ' = ';
          if (isset($value)) {
            $sqlString .= $typeAdapter->encode($this->getType($key), $value);
          }
          else {
            $sqlString .= 'NULL';
          }
        }
      }
    }
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->owner->escapeQuery($orderBy['column'])
          . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit)) {
      $sqlString .= ' LIMIT ' . $selection->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }
  
  /**
   * {@inheritdoc}
   */
  public function deleteSelection(DeleteSelection $selection) {
    $sqlString = 'DELETE FROM ' . $this->owner->quoteTableName($this->name);
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->owner->escapeQuery($orderBy['column'])
          . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit)) {
      $sqlString .= ' LIMIT ' . $selection->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }

  /**
   * {@inheritdoc}
   */
  public function insert($data) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $columns = array_keys($data);
    $values = array_values($data);
    $sqlString = 'INSERT INTO ' . $this->owner->quoteTableName($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES (';
    $first = true;
    foreach ($data as $column => $value) {
      if ($first) {
        $first = false;
      }
      else {
        $sqlString .= ', ';
      }
      if (isset($value)) {
        $sqlString .= $typeAdapter->encode($this->getType($column), $value);
      }
      else {
        $sqlString .= 'NULL';
      }
    }
    $sqlString .= ')';
    return $this->owner->rawQuery($sqlString);
  }
  
  
}
