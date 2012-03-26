<?php
class Mysql extends DatabaseDriver {

  private $handle;

  public static function connect($server, $username, $password, $options = array()) {
    $db = new self();
    $db->handle = mysql_connect($server, $username, $password, true);
    if (!$db->handle) {
      throw new DatabaseConnectionFailedException(mysql_error());
    }
    if (isset($options['database'])) {
      $db->selectDb($options['database']);
    }
    return $db;
  }

  public function __destruct() {
  }

  public function close() {
    mysql_close($this->handle);
  }

  public function selectDb($db) {
    if (!mysql_select_db($db, $this->handle)) {
      throw new DatabaseSelectFailedException(mysql_error());
    }
  }

  private function mysqlQuery($sql) {
    $result = mysql_query($sql, $this->handle);
    if (!$result) {
      throw new DatabaseQueryFailedException(mysql_error());
    }
    return $result;
//     if (preg_match('/^\\s*(update|delete) /i', $sql)) {
// //       $this->affected_rows = mysql_affected_rows($this->db_handle);
//       return  mysql_affected_rows($this->handle);
//     }
//     elseif (preg_match('/^\\s*(insert|replace) /i', $sql)) {
// //       $this->insert_id = mysql_insert_id($this->db_handle);
// //       $this->affected_rows = mysql_affected_rows($this->db_handle);
//       return mysql_affected_rows($this->dhandle);
//     }
//     elseif (preg_match('/^\\s*(select|show) /i', $sql)) {
//       return  mysql_num_rows($result);
//     }
//     else {
//       return 0;
//     }
  }

  public function execute(Query $query) {
    echo 'Execute: ' . $query->toSql($this) . '<br/>';
  }

  public function tableExists($table) {
    $result = $this->mysqlQuery("SHOW TABLES LIKE '" . $this->tableName($table) . "'");
    if (mysql_num_rows($result) >= 1)
      return true;
    else
      return false;
  }

  public function getColumns($table) {
    $result = $this->mysqlQuery("SHOW COLUMNS FROM `" . $this->tableName($table) . "`");
    $columns = array();
    while ($row = mysql_fetch_array($result)) {
      $columns[] = $row['Field'];
//       $fieldArr = explode('_', $column);
//       $field = $fieldArr[count($fieldArr) - 1];
//       $columns[$column]= $field;
    }
    return $columns;
  }

  public function escapeString($string) {
    return mysql_real_escape_string($string);
  }

}