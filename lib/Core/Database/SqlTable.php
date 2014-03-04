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
        $sqlString .= $this->owner
        ->escapeQuery($this->replaceColumns($clause['clause']),
          $clause['vars']);
      }
    }
    return $sqlString;
  }
  
  /**
   * Replace all column names of style '%table.column' or '%column' with real
   * column names
   * @param string $query Input query
   * @return string Output query
   */
  public function replaceColumns($query) {
    return preg_replace_callback(
      '/(\A|[^\\\\])%([a-z][a-z0-9_]*([.][a-z][a-z0-9_]*|[.][*])?)/i',
      array($this, 'replaceColumn'), $query);
  }
  
  /**
   * Replace a single column match
   * @param string[] $matches Matched from preg_replace_callback()
   * @return string Output column
   */
  protected function replaceColumn($matches) {
    return $matches[1] . $this->columnName($matches[2]);
  }
  
  /**
   * Get real column name. If $column includes a dot, whatever is in front of
   * the dot is prefixed and used as table name. If not and $table is set, that
   * name is prefixed and put in front of the column name, if no dot, and $table
   * is not set, the current table name is used.
   * @param string $column Column name
   * @param string $table Optional table name (unprefixed)
   * @return string A column name with prefixed table name in front, e.g.
   * 'pfrx_table.column'
   */
  public function columnName($column, $table = null) {
    if (!isset($table)) {
      $table = $this->name;
    }
    $dot = strpos($column, '.');
    if ($dot === false) {
      return $this->owner
      ->tableName($table) . '.' . $column;
    }
    else {
      $table = substr($column, 0, $dot);
      $column = substr($column, $dot + 1);
      return $this->owner
      ->tableName($table) . '.' . $column;
    }
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
    $sqlString = 'UPDATE ' . $this->owner->tableName($this->name);
    $sets = $selection->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET ';
      reset($sets);
      $first = true;
      foreach ($sets as $key => $value) {
        if ($first) {
          $first = false;
        }
        else {
          $sqlString .= ',';
        }
        if (isset($value)) {
          if ($value instanceof NoEscape) {
            $sqlString .= ' ' . $key . ' = ' . $value;
          }
          else {
            $sqlString .= ' '
              . $this->owner->escapeQuery($key . ' = ?', array($value));
          }
        }
        else {
          $sqlString .= ' ' . $key . ' = NULL';
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
    $columns = array_keys($data);
    $values = array_values($data);
    $sqlString = 'INSERT INTO ' . $this->owner->tableName($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES (';
    $first = true;
    foreach ($values as $value) {
      if ($first) {
        $first = false;
      }
      else {
        $sqlString .= ', ';
      }
      // TODO use TypeAdapter to encode...?
      if (isset($value)) {
        $sqlString .= $this->owner->escapeQuery('?', $value);
      }
      else {
        $sqlString .= 'NULL';
      }
    }
    $sqlString .= ')';
    return $this->owner->rawQuery($sqlString);
  }
  
  
}
