<?php
class SqlTable implements ITable {
  private $owner = null;
  private $name = '';
  private $schema = null;

  public function __construct(SqlDatabase $database, $table) {
    $this->owner = $database;
    $this->name = $table;
  }

  public function getName() {
    return $this->name;
  }

  public function getSchema() {
    if (!isset($this->schema)) {
      $this->schema = $this->owner->getSchema($this->name);
    }
    return $this->schema;
  }

  public function setSchema(Schema $schema = null) {
    $this->schema = $schema;
  }

  public function getOwner() {
    return $this->owner;
  }

  public function insert(InsertQuery $query = null) {
    if (!isset($query)) {
      return InsertQuery::create()->setDataSource($this);
    }
    $columns = $query->columns;
    $values = $query->values;
    $sqlString = 'INSERT INTO ' . $this->owner->tableName($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES (';
    while (($value = current($values)) !== false) {
      if (isset($value)) {
        $sqlString .= $this->owner->escapeQuery('?', $value);
      }
      else {
        $sqlString .= 'NULL';
      }
      if (next($values) !== false) {
        $sqlString .= ', ';
      }
    }
    $sqlString .= ')';
    return $this->owner->rawQuery($sqlString);
  }

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
        $sqlString .= $this->owner->escapeQuery(
          $this->replaceColumns($clause['clause']),
          $clause['vars']
        );
      }
    }
    return $sqlString;
  }

  public function replaceColumns($query) {
    return preg_replace_callback(
      '/(\A|[^\\\\])%([a-z][a-z0-9_]*([.][a-z][a-z0-9_]*)?)/i',
      array($this, 'replaceColumn'),
      $query
    );
  }

  protected function replaceColumn($matches) {
    return $matches[1] . $this->columnName($matches[2]);
  }

  public function columnName($column, $table = null) {
    if (!isset($table)) {
      $table = $this->name;
    }
    $dot = strpos($column, '.');
    if ($dot === false ) {
      return $this->owner->tableName($table) . '.' . $column;
    }
    else {
      $table = substr($column, 0, $dot);
      $column = substr($column, $dot + 1);
      return $this->owner->tableName($table) . '.' . $column;
    }
  }

  protected function getColumnList(&$value, $key) {
    $columnName = $this->replaceColumns($value['column']);
    if (isset($value['function'])) {
      $columnName = str_replace('()', '(' . $columnName . ')', $value['function']);
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
    $sqlString .= ' FROM ' . $this->owner->tableName($this->name);
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
        $sqlString .= ', ' . $this->owner->tableName($table);
        if (isset($source['alias'])) {
          $sqlString .= ' AS ' . $source['alias'];
        }
      }
    }
    if (!empty($query->joins)) {
      foreach ($query->joins as $join) {
        if (is_string($join['source'])) {
          $table = $join['source'];
        }
        else if ($join['source'] instanceof SqlTable) {
          $table = $join['source']->name;
        }
        else {
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
    if ($query->where->hasClauses()) {
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
    if (isset($query->groupBy)) {
      $columns = array();
      foreach ($query->groupBy['columns'] as $column) {
        $columns[] = $this->replaceColumns($column);
      }
      $sqlString .= ' GROUP BY ' . implode(', ', $columns);
      if (isset($query->groupBy['condition']) AND $query->groupBy['condition']->hasClauses()) {
        $sqlString .= ' HAVING ' . $this->conditionToSql($query->groupBy['condition']);
      }
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }

  public function update(UpdateQuery $query = null) {
    if (!isset($query)) {
      return UpdateQuery::create()->setDataSource($this);
    }
    $sqlString = 'UPDATE ' . $this->owner->tableName($this->name);
    $sets = $query->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET';
      reset($sets);
      while (($value = current($sets)) !== false) {
        if (isset($value)) {
          $sqlString .= ' ' . $this->owner->escapeQuery(key($sets) . ' = ?', array($value));
        }
        else {
          $sqlString .= ' ' . key($sets);
        }
        if (next($sets) !== false) {
          $sqlString .= ',';
        }
      }
    }
    if ($query->where->hasClauses()) {
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
    if (isset($this->query)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }

  public function delete(DeleteQuery $query = null) {
    if (!isset($query)) {
      return DeleteQuery::create()->setDataSource($this);
    }
    $sqlString = 'DELETE FROM ' . $this->owner->tableName($this->name);
    if ($query->where->hasClauses()) {
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
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner->rawQuery($sqlString);
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

