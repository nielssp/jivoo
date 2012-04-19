<?php
class RawQuery extends Query {
  private $vars = array();

  private $sql;

  public static function create($sql = NULL) {
    $query = new self();
    $query->sql = $sql;
    return $query;
  }

  public function setSql($sql) {
    $this->sql = $sql;
  }

  public function addTable($table) {
    $this->vars[] = array(
      'table' => $table
    );
    return $this;
  }

  public function addVar($var) {
    $this->vars[] = $var;
    return $this;
  }

  public function toSql(IDatabase $db) {
    return $db->escapeQuery($this->sql, $this->vars);
  }
}