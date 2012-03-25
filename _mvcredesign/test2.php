<?php
/**
 * Test number 2
 * Purpose: Development of database handler and query builder
 */

interface IDatabase {
  public static function connect($server, $username, $password, $options = array());
  public function close();
  public function execute(Query $query);
  public function rawQuery($sql);
  public function insertQuery($table);
  public function tableName($table);
  public function tableExists($table);
  public function getColumns($table);
}

abstract class DatabaseDriver implements IDatabase {
  protected $tablePrefix = '';

  protected function __construct() {
  }

  public function __destruct() {
    $this->close();
  }

  public function rawQuery($sql) {
    $query = RawQuery::create($sql);
    $query->setDb($this);
    return $query;
  }

  public function insertQuery($table) {
    $query = InsertQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function tableName($table) {
    return $this->tablePrefix . $table;
  }
}

class Database extends DatabaseDriver {
  private $configuration;
  private $driver;
  private $connection;

  public function __construct($configuration = NULL) {
    $this->driver = $configuration['driver'];
    $this->connection = call_user_func(
      array($this->driver, 'connect'),
      $configuration['server'],
      $configuration['username'],
      $configuration['password'],
      $configuration
    );
    if (isset($configuration['prefix'])) {
      $this->tablePrefix = $configuration['prefix'];
    }
    ActiveRecord::connect($this);
  }

  public static function getDependencies() {
    return array('configuration');
  }

  public function close() {
    $this->connection->close();
  }

  public static function connect($server, $username, $password, $options = array()) {

  }

  public function execute(Query $query) {
    return $this->connection->execute($query);
  }

  public function tableExists($table) {
    return $this->connection->tableExists($table);
  }

  public function getColumns($table) {
    return $this->connection->getColumns($table);
  }

}

class DatabaseConnectionFailedException extends Exception { }
class DatabaseSelectFailedException extends Exception { }
class DatabaseQueryFailedException extends Exception { }

class MySql extends DatabaseDriver {

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
    echo 'Execute: ' . $query->toSql() . '<br/>';
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

}

abstract class Query {

  private $db;

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
      $this->db->execute($this);
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

  public abstract function toSql();
}

class RawQuery extends Query {
  private $vars = array();

  private $sql;

  public static function create($sql) {
    $query = new self();
    $query->sql = $sql;
    return $query;
  }

  public function setString($offset, $string) {
    $this->vars[$offset] = '"' . $string . '"';
  }

  public function setInt($offset, $int) {
    $this->vars[$offset] = $int;
  }

  public function toSql() {
    $sqlString = '';
    $offset = 1;
    $prev = NULL;
    foreach (str_split($this->sql) as $char) {
      if ($char == '?' AND $prev != '\\') {
        $sqlString .= $this->vars[$offset];
        $offset++;
      }
      else {
        $sqlString .= $char;
      }
      $prev = $char;
    }
    return $sqlString;
  }
}

class InsertQuery extends Query {

  private $table;

  private $columns = array();
  private $values = array();

  public static function create($table) {
    $query = new self();
    $query->table = $table;
    return $query;
  }

  public function addColumn($column) {
    $this->columns[] = $column;
    return $this;
  }

  public function addColumns($columns) {
    if (!is_array($columns)) {
      $columns = func_get_args();
    }
    foreach ($columns as $column) {
      $this->addColumn($column);
    }
    return $this;
  }

  public function addValue($value) {
    $this->values[] = $value;
    return $this;
  }

  public function addValues($values) {
    if (!is_array($values)) {
      $values = func_get_args();
    }
    foreach ($values as $value) {
      $this->addValue($value);
    }
    return $this;
  }

  public function addPair($column, $value) {
    $this->addColumn($column);
    $this->addValue($value);
    return $this;
  }

  public function addPairs($pairs) {
    foreach ($pairs as $column => $value) {
      $this->addColumn($column);
      $this->addValue($value);
    }
    return $this;
  }

  public function toSql() {
    $sqlString = 'INSERT INTO `' . $this->tableName($this->table) . '` (';
    $sqlString .= implode(', ', $this->columns);
    $sqlString .= ') VALUES ("';
    $sqlString .= implode('", "', $this->values);
    $sqlString .= '")';
    return $sqlString;
  }

}

class SelectQuery extends Query {
  private $orderBy;
  private $descending;
  private $limit;
  private $where;
  private $offset;
  private $relation;
  private $from;

  public static function create() {
    $query = new self();
    $query->limit = -1;
    $query->offset = 0;
    $query->where = array();
    $query->relation = null;
    $query->orderBy = 'id';
    $query->descending = false;
    return $query;
  }

  public function from($from) {
    $this->from = $from;
    return $this;
  }

  public function limit($limit) {
    $this->limit = $limit;
    return $this;
  }

  public function offset($offset) {
    $this->offset = $offset;
    return $this;
  }

  public function where($column, $value) {
    $this->where[$column] = $value;
    return $this;
  }

  public function relation($table, $id) {
    $this->relation = array();
    $this->relation['table'] = $table;
    $this->relation['id'] = $id;
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy = $column;
    return $this;
  }

  public function desc() {
    $this->descending = true;
    return $this;
  }

  public function asc() {
    $this->descending = false;
    return $this;
  }

  public function toSql() {
    return '';
  }
}

$configuration = array(
  'driver' => 'MySql',
  'server' => 'localhost',
  'username' => 'mvctest',
  'password' => 'mvctest',
  'database' => 'mvctest',
  'prefix' => 'pcms_'
);

$db = new Database($configuration);

$db->insertQuery('table')
  ->addColumns('id', 'name')
  ->addValues('2', 'Niels')
  ->execute();

$query = $db->rawQuery("SELECT * FROM table WHERE user = ? AND id = ?");
$query->setString(1, 'Admin');
$query->setInt(2, 23);
$query->execute();

// or:

$query = RawQuery::create("SELECT * FROM table WHERE user = ? AND id = ?");
$query->setString(1, 'Admin');
$query->setInt(2, 23);
$db->execute($query);

function get_called_class2() {
  $bt = debug_backtrace();
  $matches = array();
  foreach ($bt as $call) {
    if (!isset($call['class'])) {
      continue;
    }
    $lines = file($call['file']);
    for ($l = $call['line']; $l > 0; $l--) {
      $line = $lines[$l - 1];
      preg_match(
        '/([a-zA-Z0-9\_]+)::' . $call['function'] . '/',
        $line,
        $matches
      );
      if (!empty($matches)) {
        break;
      }
    }
    if (!empty($matches)) {
      break;
    }
  }
  if (!isset($matches[1])) {
    return false;
  }
  if ($matches[1] == 'self' OR $matches[1] == 'parent') {
    $line = $call['line'] - 1;
    while ($line > 0 && strpos($lines[$line], 'class') === false) {
      $line--;
    }
    preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
  }
  return $matches[1];
}

class DatabaseNotConnectedException extends Exception { }

abstract class ActiveRecord {
  private static $dbConnection = NULL;
  private static $models = array();
  public static function addModel($class, $table) {
    self::$models[$class] = array('table' => $table);
  }

  private $table;
  private $data;

  public function __set($property, $value) {
    $this->data[$property] = $value;
  }

  public function __get($property) {
    return $this->data[$property];
  }

  private function __construct() {
    $db = self::connection();
    $class = get_class($this);
    $this->table = self::$models[$class]['table'];
    if (!isset(self::$models[$class]['columns'])) {
      self::$models[$class]['columns'] = array();
      $columns = $db->getColumns($this->table);
      foreach ($columns as $column) {
        $fieldArr = explode('_', $column);
        $field = $fieldArr[count($fieldArr) - 1];
        self::$models[$class]['columns'][$column] = $field;
      }
    }
    $this->data = array();
    foreach (self::$models[$class]['columns'] as $column => $field) {
      $this->data[$field] = NULL;
    }
  }

  protected static function connection() {
    if (!isset(self::$dbConnection)) {
      throw new DatabaseNotConnectedException('ActiveRecord is not connected to a database.');
    }
    return self::$dbConnection;
  }

  public static function connect(IDatabase $db) {
    self::$dbConnection = $db;
  }

  public static function create($data = array()) {
    $db = self::connection();
    $class = get_called_class2();
    $new = new $class();
    foreach ($data as $property => $value) {
      $new->$property = $value;
    }
    $query = $db->insertQuery($new->table);
    foreach (self::$models[$class]['columns'] as $column => $field) {
      if (isset($new->data[$field])) {
        $query->addPair($column, $new->data[$field]);
      }
    }
    $query->execute();
    return $new;
  }

  public function all(SelectQuery $selector = NULL) {

  }

  public function find($primaryKey = NULL) {

  }

  public function exists($primaryKey) {

  }

  public function first(SelectQuery $selector = NULL) {

  }

  public function last(SelectQuery $selector = NULL) {

  }

  public function count(SelectQuery $selector = NULL) {

  }
}

ActiveRecord::addModel('Post', 'posts');

class Post extends ActiveRecord {
  protected $validate = array(
    'title' => array('presence' => true,
                     'minLength' => 4,
                     'maxLength' => 25),
    'content' => array('presence' => true),
  );
}
class Comment extends ActiveRecord {
  public static function create($array) {
    parent::create(
      $array
    );
  }
}


$post = Post::create(array(
  'title' => 'Hello',
  'content' => 'Hello, World'
));
