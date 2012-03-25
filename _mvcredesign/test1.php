<?php

mysql_connect('localhost', 'mvctest', 'mvctest');
mysql_select_db('mvctest');

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

abstract class ActiveRecord {
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
    $class = get_class($this);
    $this->table = self::$models[$class]['table'];
    if (!isset(self::$models[$class]['columns'])) {
      self::$models[$class]['columns'] = array();
      $result = mysql_query('SHOW COLUMNS FROM ' . $this->table) OR die(mysql_error());
      while ($row = mysql_fetch_array($result)) {
        $column = $row['Field'];
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

  public static function create($data = array()) {
    $class = get_called_class2();
    $new = new $class();
    foreach ($data as $property => $value) {
      $new->$property = $value;
    }
    $query = sprintf(
      'INSERT INTO `%s` (',
      mysql_real_escape_string($new->table)
    );
    $columns = array_keys(self::$models[$class]['columns']);
    $fields = array_values(self::$models[$class]['columns']);
    $query .= implode(', ', $columns);
    $query .= ') VALUES (';
    while (($field = current($fields)) !== FALSE) {
      if (!is_null($new->$field)) {
        $query .= '"' . $new->$field . '"';
      }
      else {
        $query .= 'NULL';
      }
      if (next($fields) !== FALSE) {
        $query .= ', ';
      }
    }
    $query .= ')';
    echo $query;
    //mysql_query($query);
    return $new;
  }
}

ActiveRecord::addModel('Post', 'posts');

class Post extends ActiveRecord {

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

// ?
Post::create()
  ->setTitle('Hello')
  ->setContent('Hello, World')
  ->save();

abstract class Super {
  protected $data = 'one thing';

  public function getData() {
    echo $this->data;
  }

  public function __call($method, $arguments) {
    if (count($arguments) == 0) {
      return $this->$method;
    }
    else {
      $this->$method = $arguments[0];
      return $this;
    }
  }
}

class Sub extends Super {
  protected $data = 'another thing';
}
