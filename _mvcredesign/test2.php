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
  public function selectQuery($table = NULL);
  public function updateQuery($table);
  public function tableName($table);
  public function tableExists($table);
  public function getColumns($table);
  public function escapeString($string);
  public function escapeQuery($format, $vars);
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

  public function selectQuery($table = NULL) {
    $query = SelectQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function updateQuery($table = NULL) {
    $query = SelectQuery::create($table);
    $query->setDb($this);
    return $query;
  }

  public function tableName($table) {
    return $this->tablePrefix . $table;
  }

  public function escapeQuery($format, $vars) {
    $sqlString = '';
    $key = 0;
    $chars = str_split($format);
    foreach ($chars as $offset => $char) {
      if ($char == '?' AND (!isset($chars[$offset - 1]) OR $chars[$offset - 1] != '\\')) {
        if (is_array($vars[$key]) AND isset($vars[$key]['table'])) {
          $sqlString .=  $this->tableName($vars[$key]['table']);
        }
        else if (is_int($vars[$key])) {
          $sqlString .= (int)$vars[$key];
        }
        else {
          $sqlString .= '"' . $this->escapeString($vars[$key]) . '"';
        }
        $key++;
      }
      else if ($char != '\\' OR !isset($chars[$offset + 1]) OR $chars[$offset + 1] != '?') {
        $sqlString .= $char;
      }
    }
    return $sqlString;
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
    $this->connection->tablePrefix = $configuration['prefix'];
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

  public function escapeString($string) {
    return $this->connection->escapeString($string);
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

  public abstract function toSql(IDatabase $db);


}

class RawQuery extends Query {
  private $vars = array();

  private $sql;

  public static function create($sql) {
    $query = new self();
    $query->sql = $sql;
    return $query;
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

  public function toSql(IDatabase $db) {
    $sqlString = 'INSERT INTO ' . $db->tableName($this->table) . ' (';
    $sqlString .= implode(', ', $this->columns);
    $sqlString .= ') VALUES (';
    while (($value= current($this->values)) !== FALSE) {
      if (isset($value)) {
        $sqlString .= '"' . $db->escapeString($value) . '"';
      }
      else {
        $sqlString .= 'NULL';
      }
      if (next($this->values) !== FALSE) {
        $sqlString .= ', ';
      }
    }
    $sqlString .= ')';
    return $sqlString;
  }

}

class SelectQuery extends Query {
  private $orderBy;
  private $descending;
  private $limit;
  private $where;
  private $whereVars;
  private $offset;
  private $relation;
  private $table;
  private $columns = array();

  public static function create($table = NULL) {
    $query = new self();
    $query->offset = 0;
    $query->descending = FALSE;
    $query->table = $table;
    return $query;
  }

  public function from($table) {
    $this->table = $table;
    return $this;
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

  public function limit($limit) {
    $this->limit = (int)$limit;
    return $this;
  }

  public function offset($offset) {
    $this->offset = (int)$offset;
    return $this;
  }

  public function where($clause) {
    $this->where = $clause;
    return $this;
  }

  public function addVar($var) {
    $this->whereVars[] = $var;
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy = $column;
    $this->descending = false;
    return $this;
  }

  public function orderByDescending($column) {
    $this->orderBy = $column;
    $this->descending = true;
    return $this;
  }

  public function toSql(IDatabase $db) {
    $sqlString = 'SELECT ';
    if (!empty($this->columns)) {
      $sqlString .= implode(', ', $this->columns);
    }
    else {
      $sqlString .= '*';
    }
    $sqlString .= ' FROM ' . $db->tableName($this->table);
    if (isset($this->where)) {
      $sqlString .= ' WHERE ' . $db->escapeQuery($this->where, $this->whereVars);
    }
    if (isset($this->orderBy)) {
      $sqlString .= ' ORDER BY ' . $this->orderBy;
      $sqlString .= $this->descending ? ' DESC' : ' ASC';
    }
    if (isset($this->limit)) {
      $sqlString .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
    }
    return $sqlString;
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
  ->addValues('2', 'N"i"els')
  ->execute();

$query = $db->rawQuery("SELECT * FROM ? WHERE user = ? AND id = ?");
$query->addTable('table');
$query->addVar('Ad"m"in');
$query->addVar(23);
$query->execute();

// or:

$query = RawQuery::create("SELECT * FROM ? WHERE user = ? AND id = ?");
$query->addTable('table');
$query->addVar('Admin');
$query->addVar(23);
$db->execute($query);

$query = $db->selectQuery('table')
  ->addColumns('id', 'user')
  ->where('user = ? AND id = ?')
  ->addVar('Admin')
  ->addVar(32)
  ->orderBy('id')
  ->limit(30)
  ->offset(2)
  ->execute();

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
    if (array_key_exists($property, $this->data)) {
      $this->data[$property] = $value;
    }
    else {

    }
  }

  public function __get($property) {
    if (array_key_exists($property, $this->data)) {
      return $this->data[$property];
    }
    else {

    }
  }

  private function __construct() {
    $db = self::connection();
    $class = get_class($this);
    $this->table = self::$models[$class]['table'];
    if (!isset(self::$models[$class]['columns'])) {
      self::$models[$class]['columns'] = $db->getColumns($this->table);
//       foreach ($columns as $column) {
//         $fieldArr = explode('_', $column);
//         $field = $fieldArr[count($fieldArr) - 1];
//         self::$models[$class]['columns'][$column] = $field;
//       }
    }
    $this->data = array();
    foreach (self::$models[$class]['columns'] as $column) {
      $this->data[$column] = NULL;
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
//     foreach (self::$models[$class]['columns'] as $column => $field) {
//       if (isset($new->data[$field])) {
//         $query->addPair($column, $new->data[$field]);
//       }
//     }
    $new->id = $query->addPairs($new->data)->execute();
    return $new;
  }

  public function save() {

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

}


$post = Post::create(array(
  'title' => 'Hello',
  'content' => 'Hello", World'
));
