<?php
class SqlTable implements ITable {
  private $owner = NULL;
  private $name = '';
  private $schema = NULL;

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

  public function setSchema(Schema $schema = NULL) {
    $this->schema = $schema;
  }

  public function getOwner() {
    return $this->owner;
  }

  public function insert(InsertQuery $query = NULL) {
    if (!isset($query)) {
      return InsertQuery::create()->setDataSource($this);
    }
    $columns = $query->columns;
    $values = $query->values;
    $sqlString = 'INSERT INTO ' . $this->owner->tableName($this->name) . ' (';
    $sqlString .= implode(', ', $columns);
    $sqlString .= ') VALUES (';
    while (($value = current($values)) !== FALSE) {
      if (isset($value)) {
        $sqlString .= $this->owner->escapeQuery('?', $value);
      }
      else {
        $sqlString .= 'NULL';
      }
      if (next($values) !== FALSE) {
        $sqlString .= ', ';
      }
    }
    $sqlString .= ')';
    return $this->owner->rawQuery($sqlString);
  }

  public function select(SelectQuery $query = NULL) {
    if (!isset($query)) {
      return SelectQuery::create()->setDataSource($this);
    }
    $sqlString = 'SELECT ';
    if (!empty($query->columns)) {
      $sqlString .= $query->count ? 'COUNT(' : '';
      $sqlString .= implode($query->count ? '), COUNT(' : ', ', $query->columns);
      $sqlString .= $query->count ? ')' : '';
    }
    else {
      $sqlString .= $query->count ? 'COUNT(*)' : '*';
    }
    $sqlString .= ' FROM ' . $this->owner->tableName($this->name);
    if (isset($query->join)) {
      $sqlString .= ' JOIN ' . $this->owner->tableName($query->join['table']);
      $sqlString .= ' ON ' . $query->join['left'] . ' = ' . $query->join['right'];
    }
    if (!empty($query->where)) {
      $sqlString .= ' WHERE ' . $this->owner->escapeQuery($query->where, $query->whereVars);
    }
    if (isset($query->orderBy)) {
      $sqlString .= ' ORDER BY ' . $query->orderBy;
      $sqlString .= $query->descending ? ' DESC' : ' ASC';
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }

  public function update(UpdateQuery $query = NULL) {
    if (!isset($query)) {
      return UpdateQuery::create()->setDataSource($this);
    }
    $sqlString = 'UPDATE ' . $this->owner->tableName($this->name);
    $sets = $query->sets;
    if (!empty($sets)) {
      $sqlString .= ' SET';
      reset($sets);
      while (($value = current($sets)) !== FALSE) {
        $sqlString .= ' ' . $this->owner->escapeQuery(key($sets) . ' = ?', array($value));
        if (next($sets) !== FALSE) {
          $sqlString .= ',';
        }
      }
    }
    if (isset($query->where)) {
      $sqlString .= ' WHERE ' . $this->owner->escapeQuery($query->where, $query->whereVars);
    }
    if (isset($query->orderBy)) {
      $sqlString .= ' ORDER BY ' . $query->orderBy;
      $sqlString .= $query->descending ? ' DESC' : ' ASC';
    }
    if (isset($this->query)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }

  public function delete(DeleteQuery $query = NULL) {
    if (!isset($query)) {
      return DeleteQuery::create()->setDataSource($this);
    }
    $sqlString = 'DELETE FROM ' . $this->owner->tableName($this->name);
    if (isset($query->join)) {
      $sqlString .= ' JOIN ' . $this->owner->tableName($query->join['table']);
      $sqlString .= ' ON ' . $query->join['left'] . ' = ' . $query->join['right'];
    }
    if (isset($query->where)) {
      $sqlString .= ' WHERE ' . $this->owner->escapeQuery($query->where, $query->whereVars);
    }
    if (isset($query->orderBy)) {
      $sqlString .= ' ORDER BY ' . $query->orderBy;
      $sqlString .= $query->descending ? ' DESC' : ' ASC';
    }
    if (isset($query->limit)) {
      $sqlString .= ' LIMIT ' . $query->offset . ', ' . $query->limit;
    }
    return $this->owner->rawQuery($sqlString);
  }

  public function count(SelectQuery $query = NULL) {
    if (!isset($query)) {
      $query = new SelectQuery();
    }
    $result = $this->select($query->count());
    if (!$result->hasRows()) {
      return FALSE;
    }
    $row = $result->fetchRow();
    return $row[0];
  }
}

