<?php
require '../app/essentials.php';

require p(CLASSES . 'database/MysqlDatabase.php');

class DummySqlDatabase extends SqlDatabase {
  public function __construct($options = array()) { }
  public function close() { }
  public function tableExists($table) { return true; }
  public function getSchema($table) { }
  public function escapeString($string) { return addslashes($string); }
  public function tableName($name) { return 'pre_' . $name; }
  public function rawQuery($string) { var_dump($string); }
  public function createTable(Schema $schema) { }
  public function dropTable($table) { }
  public function addColumn($table, $column, $options = array()) { }
  public function deleteColumn($table, $column) { }
  public function alterColumn($table, $column, $options = array()) { }
  public function createIndex($table, $index, $options = array()) { }
  public function deleteIndex($table, $index) { }
  public function alterIndex($table, $index, $options = array()) { }
}

$db = new DummySqlDatabase();
$table1 = $db->imaginary;
$table2 = $db->secondary;
$select = new SelectQuery();
$select->addColumn('%ass');
$select->addColumn('sec.ass', 'ass2', 'YEAR(FROM_UNIXTIME())');
$select->innerJoin('secondary', '%secondary.ass = ass2');
$select->or(Condition::create('%ass = ?', 2.4)->and('ass2 IS null'));
$select->groupBy(array('ass', 'ass2'), 'ass = 2');

echo '<pre>';

$table1->select($select);

$table1->count($select);

echo '</pre>';
