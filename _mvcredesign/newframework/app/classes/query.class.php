<?php
abstract class Query {

  protected $db;

  protected function __construct() {
  }

  public static function create() {
    return new self();
  }

  public function setDb(IDatabase $db) {
    $this->db = $db;
  }

  public function execute() {
    if (isset($this->db) AND $this->db instanceof IDatabase) {
      return $this->db->execute($this);
    }
    else {
      throw new Exception('No database to execute on');
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

  public abstract function toSql(IDatabase $db);


}