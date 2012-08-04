<?php
abstract class Query {
  private $dataSource = NULL;

  public function __construct() {
  }

  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
  }

  public function __isset($property) {
    return isset($this->$property);
  }

  public static function create() {
    $class = get_called_class();
    return new $class();
  }

  public function setDataSource(IDataSource $dataSource) {
    $this->dataSource = $dataSource;
    return $this;
  }

  public function execute() {
    if (isset($this->dataSource)) {
      if ($this instanceof InsertQuery) {
        return $this->dataSource->insert($this);
      }
      else if ($this instanceof SelectQuery) {
        return $this->dataSource->select($this);
      }
      else if ($this instanceof UpdateQuery) {
        return $this->dataSource->update($this);
      }
      else if ($this instanceof DeleteQuery) {
        return $this->dataSource->delete($this);
      }
    }
    else if (isset($this->db) AND $this->db instanceof IDatabase) {
      return $this->db->execute($this);
    }
    else {
      throw new Exception('No data source to execute on');
    }
  }

  protected function tableName($table) {
    if (isset($this->db) AND $this->db instanceof IDatabase) {
      return $this->db->tableName($table);
    }
    else {
      return $table;
    }
  }

}
