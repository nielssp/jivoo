<?php
/**
 * Table implementation for {@see SqlDatabase} classes
 * @package Core\Database
 */
class SqlTable implements ITable {
  /**
   * @var SqlDatabase Owner database
   */
  protected $owner = null;
  
  /**
   * @var string Table name (without prefix)
   */
  protected $name = '';
  
  /**
   * @var Schema|null Table schema if set
   */
  protected $schema = null;

  /**
   * Constructor.
   * @param SqlDatabase $database Owner database
   * @param string $table Table name (without prefix)
   */
  public function __construct(SqlDatabase $database, $table) {
    $this->owner = $database;
    $this->name = $table;
  }

  public function getName() {
    return $this->name;
  }
  
  public function getPrimaryKey() {
    return $this->getSchema()->getPrimaryKey();
  }

  public function getSchema() {
    if (!isset($this->schema)) {
      $this->schema = $this->owner
        ->getSchema($this->name);
    }
    return $this->schema;
  }

  public function setSchema(Schema $schema = null) {
    $this->schema = $schema;
  }

  /**
   * @return SqlDatabase Owner database
   */
  public function getOwner() {
    return $this->owner;
  }

  public function insert(InsertQuery $query = null) {
    if (!isset($query)) {
      return InsertQuery::create()->setDataSource($this);
    }
    $columns = $query->columns;
    $values = $query->values;
    $sqlString = 'INSERT INTO ' . $this->owner
          ->tableName($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES (';
    while (($value = current($values)) !== false) {
      if (isset($value)) {
        $sqlString .= $this->owner
          ->escapeQuery('?', $value);
      }
      else {
        $sqlString .= 'NULL';
      }
      if (next($values) !== false) {
        $sqlString .= ', ';
      }
    }
    $sqlString .= ')';
    return $this->owner
      ->rawQuery($sqlString);
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
      '/(\A|[^\\\\])%([a-z][a-z0-9_]*([.][a-z][a-z0-9_]*)?)/i',
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
    $columnName = $this->replaceColumns($value['column']);
    if (isset($value['function'])) {
      $columnName = str_replace('()', '(' . $columnName . ')',
        $value['function']);
    }
    if (isset($value['alias'])) {
      $value = $columnName . ' AS ' . $value['alias'];
    }
    else {
      $value = $columnName;
    }
  }

  public function select(SelectQuery $query = null) {
    if (!isset($query)) {
      return SelectQuery::create()->setDataSource($this);
    }
    $sqlString = 'SELECT ';
    if (!empty($query->columns)) {
      $columns = $query->columns;
      array_walk($columns, array($this, 'getColumnList'));
      $sqlString .= implode(', ', $columns);
    }
    else {
      $sqlString .= '*';
    }
    $sqlString .= ' FROM ' . $this->owner
          ->tableName($this->name);
    if (!empty($query->sources)) {
      foreach ($query->sources as $source) {
        if (is_string($source['source'])) {
          $table = $source['source'];
        }
        else if ($source['source'] instanceof SqlTable) {
          $table = $source['source']->name;
        }
        else {
          continue;
        }
        $sqlString .= ', ' . $this->owner
              ->tableName($table);
        if (isset($source['alias'])) {
          $sqlString .= ' AS ' . $source['alias'];
        }
      }
    }
    if (!empty($query->joins)) {
      foreach ($query->joins as $join) {
        if ($join['source'] instanceof SqlTable) {
          if ($join['source']->getOwner() !== $this->owner) {
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
        $sqlString .= ' ' . $join['type'] . ' JOIN '
          . $this->owner->tableName($table);
        if (isset($join['alias'])) {
          $sqlString .= ' AS ' . $join['alias'];
        }
        if (isset($join['condition']) AND $join['condition']->hasClauses()) {
          $sqlString .= ' ON ' . $this->conditionToSql($join['condition']);
        }
      }
    }
    if ($query->where
      ->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($query->where);
    }
    if (isset($query->groupBy)) {
      $columns = array();
      foreach ($query->groupBy['columns'] as $column) {
        $columns[] = $this->replaceColumns($column);
      }
      $sqlString .= ' GROUP BY ' . implode(', ', $columns);
      if (isset($query->groupBy['condition'])
          AND $query->groupBy['condition']
            ->hasClauses()) {
        $sqlString .= ' HAVING '
            . $this->conditionToSql($query->groupBy['condition']);
      }
    }
    if (!empty($query->orderBy)) {
      $columns = array();
      foreach ($query->orderBy as $orderBy) {
        $columns[] = $this->replaceColumns($orderBy['column'])
            . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function update(UpdateQuery $query = null) {
    if (!isset($query)) {
      return UpdateQuery::create()->setDataSource($this);
    }
    $sqlString = 'UPDATE ' . $this->owner
          ->tableName($this->name);
    $sets = $query->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET';
      reset($sets);
      while (($value = current($sets)) !== false) {
        if (isset($value)) {
          if ($value instanceof NoEscape) {
            $sqlString .= ' ' . key($sets) . ' = ' . $value;
          }
          else {
            $sqlString .= ' '
              . $this->owner
              ->escapeQuery(key($sets) . ' = ?', array($value));
          }
        }
        else {
          $sqlString .= ' ' . key($sets) . ' = NULL';
        }
        if (next($sets) !== false) {
          $sqlString .= ',';
        }
      }
    }
    if ($query->where
      ->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($query->where);
    }
    if (!empty($query->orderBy)) {
      $columns = array();
      foreach ($query->orderBy as $orderBy) {
        $columns[] = $this->replaceColumns($orderBy['column'])
            . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->limit;
    }
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function delete(DeleteQuery $query = null) {
    if (!isset($query)) {
      return DeleteQuery::create()->setDataSource($this);
    }
    $sqlString = 'DELETE FROM ' . $this->owner
          ->tableName($this->name);
    if ($query->where
      ->hasClauses()) {
      $sqlString .= ' WHERE ' . $this->conditionToSql($query->where);
    }
    if (!empty($query->orderBy)) {
      $columns = array();
      foreach ($query->orderBy as $orderBy) {
        $columns[] = $this->replaceColumns($orderBy['column'])
            . ($orderBy['descending'] ? ' DESC' : ' ASC');
      }
      $sqlString .= ' ORDER BY ' . implode(', ', $columns);
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->limit;
    }
    return $this->owner
      ->rawQuery($sqlString);
  }

  public function count(SelectQuery $query = null) {
    if (!isset($query)) {
      $query = new SelectQuery();
    }
    else {
      $query = clone $query;
    }
    $result = $this->select($query->count());
    if (!$result->hasRows()) {
      return false;
    }
    $row = $result->fetchRow();
    return $row[0];
  }
}

