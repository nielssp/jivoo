<?php
include '../lib/Core/bootstrap.php';

Lib::import('Core');
Lib::import('Core\Database');

/**
 * Represents a database table schema
 * @package Core\Database
 */
class Schema2 {
  const UNSIGNED = 0x1;
  const AUTO_INCREMENT = 0x2;
  const NOT_NULL = 0x4;
  
  private static $defaults = array(
    'type' => 'text',
    'length' => null,
    'null' => true,
    'unsigned' => false,
    'autoIncrement' => false,
    'default' => null
  );

  private $schema = array();
  private $columns = array();
  private $readOnly = false;
  private $name = 'undefined';

  /**
   * @var array List of indexes in format `array(
   *   'indexname' => array(
   *     'columns' => array('columnname1', 'columnname2'),
   *     'unique' => true
   *   )
   * )`
   */
  private $indexes = array();

  /**
   * Create schema
   * @param string $name Name of schema
  */
  public function __construct($name = null) {
    $className = get_class($this);
    if ($className != __CLASS__) {
      if (!isset($name)) {
        $name = substr($className, 0, -6);
      }
      $this->createSchema();
      $this->readOnly = true;
    }
    if (isset($name)) {
      $this->name = $name;
    }
  }

  /**
   * Get information about column
   * @param string $column Column name
   * @return array Key/value pairs with possible keys:
   * 'type', 'length', 'null', 'default', 'autoIncrement', 'key', 'unsigned'
   */
  public function __get($column) {
    if (isset($this->schema[$column])) {
      return $this->schema[$column];
    }
  }

  /**
   * Whether or not a column exists in schema
   * @param string $column Column name
   * @return bool True if it does, false otherwise
   */
  public function __isset($column) {
    return isset($this->schema[$column]);
  }

  /**
   * Get name of schema
   * @return string Name
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Create schema
   */
  protected function createSchema() { }

  /**
   * Add a column to schema
   * @param string $column Column name
   * @param array $info Column information
   */
  public function addColumn($column, $info = array()) {
    if (!$this->readOnly) {
      array_merge(self::$defaults, $info);
      $this->columns[] = $column;
      $this->schema[$column] = $info;
    }
  }

  public function addString($name, $length = 255, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'string',
      'length' => $length,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function addInteger($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'integer',
      'length' => null,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => ($flags & self::AUTO_INCREMENT) != 0,
      'unsigned' => ($flags & self::UNSIGNED) != 0,
    );
  }

  public function addFloat($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'float',
      'length' => null,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function addBoolean($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'boolean',
      'length' => null,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function addText($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'text',
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function addBinary($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'binary',
      'length' => null,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function addDate($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'date',
      'length' => null,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function addDateTime($name, $flags = 0, $default = null) {
    $this->columns[] = $name;
    $this->schema[$name] = array(
      'type' => 'dateTime',
      'length' => null,
      'null' => ($flags & self::NOT_NULL) == 0,
      'default' => $default,
      'autoIncrement' => false,
      'unsigned' => false,
    );
  }

  public function setPrimaryKey($columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) <= 1) {
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    $this->indexes['PRIMARY'] = array(
      'columns' => $columns,
      'unique' => true
    );
  }
  
  public function getPrimaryKey() {
    if (!isset($this->indexes['PRIMARY'])) {
      return array();
    }
    return $this->indexes['PRIMARY']['columns'];
  }
  
  public function isPrimaryKey($column) {
    if (!isset($this->indexes['PRIMARY'])) {
      return false;
    }
    return in_array($column, $this->indexes['PRIMARY']['columns']);
  }

  /**
   * Add a unique index to schema
   * @param string $index Index name
   * @param string|string[] $columns An array of column names
   * @param string $columns,... Additional column names
   */
  public function addUnique($name, $columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) <= 2) {
        array_shift($params);
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    if (isset($this->indexes[$name])) {
      $columns = array_merge($this->indexes[$name]['columns'], $columns);
    }
    $this->indexes[$name] = array(
      'columns' => $columns,
      'unique' => true
    );
  }

  /**
   * Add an index to schema
   * @param string $index Index name
   * @param string|string[] $columns An array of column names
   * @param string $columns,... Additional column names
   */
  public function addIndex($name, $columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) <= 2) {
        array_shift($params);
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    if (isset($this->indexes[$name])) {
      $columns = array_merge($this->indexes[$name]['columns'], $columns);
    }
    $this->indexes[$name] = array(
      'columns' => $columns,
      'unique' => false
    );
  }

  /**
   * Get column names
   * @return string[] Column names
   */
  public function getColumns() {
    return $this->columns;
  }
  
  public function getIndexes() {
    return $this->indexes;
  }
  
  public function indexExists($name) {
    return isset($this->indexes[$name]);
  }
  
  public function getIndex($name) {
    return $this->indexes[$name];
  }
  
  /**
   * Export schema to PHP class
   * @param string $package Package (for documentation)
   * @return string PHP source
   */
  public function export($package = 'Core') {
    $source = '<?php' . PHP_EOL;
    $source .= '/**' . PHP_EOL;
    $source .= ' * Automatically generated schema for ' . $this->name
    . ' table' . PHP_EOL;
    $source .= ' * @package ' . $package . PHP_EOL;
    $source .= ' */' . PHP_EOL;
    $source .= 'class ' . $this->name . 'Schema extends Schema {' . PHP_EOL;
    $source .= '  protected function createSchema() {' . PHP_EOL;
    foreach ($this->schema as $column => $info) {
      $source .= '    $this->add' . ucfirst($info['type']) . '(';
      $source .= var_export($column, true);
      if ($info['type'] == 'string') {
        $source .= ', ' . var_export($info['length'], true);
      }
      $flags = array();
      if (isset($info['autoIncrement']) AND $info['autoIncrement']) {
        $flags[] = 'Schema::AUTO_INCREMENT';
      }
      if (isset($info['null']) AND !$info['null']) {
        $flags[] = 'Schema::NOT_NULL';
      }
      if (isset($info['unsigned']) AND $info['unsigned']) {
        $flags[] = 'Schema::UNSIGNED';
      }
      if (!empty($flags) OR isset($info['default'])) {
        if (empty($flags)) {
          $source .= ', 0';
        }
        else {
          $source .= ', ' . implode(' | ', $flags);
        } 
        if (isset($info['default'])) {
          $source .= ', ' . var_export($info['default'], true);
        }
      }
      $source .= ');' . PHP_EOL;
    }
  
    if (isset($this->indexes['PRIMARY'])) {
      $primaryKeyColumns = array();
      foreach ($this->indexes['PRIMARY']['columns'] as $column) {
        $primaryKeyColumns[] = var_export($column, true);
      }
      $source .= '    $this->setPrimaryKey(' . implode(', ', $primaryKeyColumns);
      $source .= ');' . PHP_EOL;
    }
    foreach ($this->indexes as $index => $info) {
      if ($index == 'PRIMARY') {
        continue;
      }
      if ($info['unique']) {
        $source .= '    $this->addUnique(' . var_export($index, true);
      }
      else {
        $source .= '    $this->addIndex(' . var_export($index, true);
      }
      foreach ($info['columns'] as $column) {
        $source .= ', ' . var_export($column, true);
      }
      $source .= ');' . PHP_EOL;
    }
    $source .= '  }' . PHP_EOL;
    $source .= '}' . PHP_EOL;
    return $source;
  }
}

abstract class Enum {
  protected $values = array();
  private $value = null;
  private $ordinal = null;
  
  public final function __construct($value) {
    $ordinal = array_search($value, $this->values);
    if ($ordinal === false) {
      throw new EnumValueInvalidException('Invalid enum value');
    }
    $this->ordinal = $ordinal;
    $this->value = $value;
  }
  
  public final function toOrdinal() {
    return $this->ordinal;
  }
  
  public final function toString() {
    return $this->value;
  }
  
  public final function __toString() {
    return $this->value;
  }
}

class EnumValueInvalidException extends Exception { }

final class DataType extends Enum {  
  protected $values = array('text', 'string', 'integer');
}

$type = new DataType('integer');
// $type = DataType::TEXT;
// $type = Schema::TYPE_TEXT;

// example schema file:

function something(DataType $type) {
  switch ($type) {
    case 'text':
      echo 'text';
      break;
    default:
      echo $type;
  }
}

class usersSchema extends Schema {
  protected function createSchema() {
    $this->addInteger('id', Schema::UNSIGNED | Schema::AUTO_INCREMENT | Schema::NOT_NULL);
    $this->addString('username', 255, Schema::NOT_NULL);
    $this->addString('password', 255, Schema::NOT_NULL);
    $this->addString('session', 255, Schema::NOT_NULL);
    $this->addInteger('hue', Schema::UNSIGNED | Schema::NOT_NULL);
    $this->addDateTime('created_at');
    $this->addDateTime('updated_at');
    $this->setPrimaryKey('id');
    $this->addUnique('username', 'username');
    $this->addIndex('session', 'session');
  }
}

class users2Schema extends Schema2 {
  protected function createSchema() {
    $this->addInteger('id', Schema::UNSIGNED | Schema::AUTO_INCREMENT | Schema::NOT_NULL);
    $this->addString('username', 255, Schema::NOT_NULL);
    $this->addString('password', 255, Schema::NOT_NULL);
    $this->addString('session', 255, Schema::NOT_NULL);
    $this->addInteger('hue', Schema::UNSIGNED | Schema::NOT_NULL);
    $this->addDateTime('created_at');
    $this->addDateTime('updated_at');
    $this->setPrimaryKey('id');
    $this->addUnique('username', 'username');
    $this->addIndex('session', 'session');
  }
}

header('Content-Type: text/plain');

// $p = '../../acrum/app/schemas';
// $dir = opendir($p);
// while ($file = readdir($dir)) {
//   $ext = explode('.', $file);
//   if ($ext[1] == 'php') {
//     $class = $ext[0];
//     require $p . '/' . $file;
//     $obj = new $class();
//     $fp = fopen($p . '/' . $file, 'w');
//     $c = $obj->export('Acrum\\Schemas');
//     echo $c;
//     fwrite($fp, $c);
//     fclose($fp);
//   }
// }

include '../../LAB/LabTest.php';


$test = new LabTest();


$rounds = 1000;

function create_schema_1() {
  return new usersSchema();
}

function create_schema_2() {
  return new users2Schema();
}

$test->testFunction($rounds, 'create_schema_1');

$test->testFunction($rounds, 'create_schema_2');

$schema = new Schema();
$schema->setPrimaryKey('test', 'as');
$schema->addIndex('test', 'test', '2', '3');
$test->dump($schema->getPrimaryKey());
$test->dump($schema->getIndex('test'));
$test->report();

