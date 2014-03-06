<?php
class SqlTable extends Table {
  /**
   * @var SqlDatabase Owner database
   */
  private $owner = null;

  /**
   * @var string Table name (without prefix)
   */
  private $name = '';

  /**
   * @var Schema|null Table schema if set
   */
  private $schema = null;

  /**
   * Constructor.
   * @param SqlDatabase $database Owner database
   * @param string $table Table name (without prefix)
   */
  public function __construct(SqlDatabase $database, $table, ISchema $schema) {
    $this->owner = $database;
    $this->name = $table;
    $this->schema = $schema;
    parent::__construct();
  }

  public function getName() {
    return $this->name;
  }

  public function getSchema() {
    return $this->schema;
  }

  public function setSchema(Schema $schema = null) {
    $this->schema = $schema;
  }
  

  public function createExisting($data = array()) {
    $typeAdapter = $this->owner->getTypeAdapter();
    foreach ($data as $field => $value) {
      $data[$field] = $typeAdapter->decode($this->getType($field), $value);
    }
    return Record::createExisting($this, $data);
  }

  /**
   * Convert a condition to SQL
   * @param Condition $where The condition
   * @return string SQL subquery
   */
  protected function conditionToSql(Condition $where) {
    $sqlString = '';
    foreach ($where->clauses as $clause) {
      if ($sqlString != '') {
        $sqlString .= ' ' . $clause['glue'] . ' ';
      }
      if ($clause['clause'] instanceof Condition) {
        if ($clause['clause']->hasClauses()) {
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
   * For use with array_walk(), will run {@see SqlTable::replaceColumns()} on
   * each column in an array. The input $value should be an associative array
   * as described in the documentation for {@see SelectQuery::$columns}.
   * The resulting $value vil be a string.
   * @param array $value Array reference
   * @param mixed $key Key (not used)
   */
  protected function getColumnList(&$value, $key) {
    $expression = $this->replaceColumns($value['expression']);
    if (isset($value['alias'])) {
      $value = $expression . ' AS ' . $value['alias'];
    }
    else {
      $value = $expression;
    }
  }

  public function readSelection(ReadSelection $selection) {
    $sqlString = 'SELECT ';
    if (!empty($selection->fields)) {
      $fields = $selection->fields;
      array_walk($fields, array($this, 'getColumnList'));
      $sqlString .= implode(', ', $fields);
    }
    else {
      $sqlString .= $this->owner->tableName($this->name) . '.*';
    }
    $sqlString .= ' FROM ' . $this->owner->tableName($this->name);
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
        $sqlString .= ', ' . $this->owner->tableName($table);
        if (isset($source['alias'])) {
          $sqlString .= ' AS ' . $source['alias'];
        }
      }
    }
    if (!empty($selection->joins)) {
      foreach ($selection->joins as $join) {
        if ($join['source'] instanceof SqlTable) {
          if ($join['source']->owner !== $this->owner) {
            throw new Exception(tr(
              'Unable to join SqlTable with table of different database'
            ));
          }
          $table = $join['source']->name;
        }
        else {
          throw new Exception(tr(
            'Unable to join SqlTable with data source of type "%1"',
            get_class($join['source'])
          ));
          continue;
        }
        $sqlString .= ' ' . $join['type'] . ' JOIN ' . $this->owner->tableName($table);
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
        $columns[] = $this->replaceColumns($column);
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
        $columns[] = $this->replaceColumns($orderBy['column'])
        . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit)) {
      $sqlString .= ' LIMIT ' . $selection->offset . ', ' . $selection->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }
  /**
   * @param UpdateSelection $selection
   * @return int Number of affected records
  */
  public function updateSelection(UpdateSelection $selection) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $sqlString = 'UPDATE ' . $this->owner->tableName($this->name);
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
        $sqlString .= ' ' . $key . ' = ';
        if (isset($value)) {
          if ($value instanceof NoEscape) {
            $sqlString .= $value;
          }
          else {
            $sqlString .= $typeAdapter->encode($this->getType($key), $value);
          }
        }
        else {
          $sqlString .= 'NULL';
        }
      }
    }
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->replaceColumns($orderBy['column'])
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
   * @param DeleteSelection $selection
   * @return int Number of affected records
  */
  public function deleteSelection(DeleteSelection $selection) {
    $sqlString = 'DELETE FROM ' . $this->owner->tableName($this->name);
    if ($selection->where->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($selection->where);
    }
    if (!empty($selection->orderBy)) {
      $columns = array();
      foreach ($selection->orderBy as $orderBy) {
        $columns[] = $this->replaceColumns($orderBy['column'])
          . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($selection->limit)) {
      $sqlString .= ' LIMIT ' . $selection->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }
  
  public function insert($data) {
    $typeAdapter = $this->owner->getTypeAdapter();
    $columns = array_keys($data);
    $values = array_values($data);
    $sqlString = 'INSERT INTO ' . $this->owner->tableName($this->name) . ' (';
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
        $value = $typeAdapter->encode($this->getType($column), $value);
        $sqlString .= $this->owner->quoteString($value);
      }
      else {
        $sqlString .= 'NULL';
      }
    }
    $sqlString .= ')';
    return $this->owner->rawQuery($sqlString);
  }
  
  
}
