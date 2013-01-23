<?php
/**
 * Represents a database table schema
 * @package PeanutCMS
 */
class Schema {
  private $_schema = array();
  private $_columns = array();
  private $_primaryKey = null;
  private $_readOnly = false;
  private $_name = 'undefined';

  /**
   * @var array List of indexes in format `array(
   *   'indexname' => array(
   *     'columnts' => array('columnname1', 'columnname2'),
   *     'unique' => true
   *   )
   * )
   */
  public $indexes = array();

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
      $classVars = get_class_vars($className);
      foreach ($classVars as $key => $value) {
        if ($key[0] != '_' AND $key != 'indexes') {
          $this->_schema[$key] = $value;
          $this->_columns[] = $key;
        }
      }
      $this->_readOnly = true;
    }
    if (isset($name)) {
      $this->_name = $name;
    }
  }

  /**
   * Get information about column
   * @param string $column Column name
   * @return array Key/value pairs with possible keys:
   * 'type', 'length', 'null', 'default', 'autoIncrement', 'key'
   */
  public function __get($column) {
    if (isset($this->_schema[$column])) {
      return $this->_schema[$column];
    }
  }

  /**
   * Whether or not a column exists in schema
   * @param string $column Column name
   * @return bool True if it does, false otherwise
   */
  public function __isset($column) {
    return isset($this->_schema[$column]);
  }

  /**
   * Get name of schema
   * @return string Name
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * Add a column to schema
   * @param string $column Column name
   * @param array $info Column information
   */
  public function addColumn($column, $info = array()) {
    if (!$this->_readOnly) {
      $this->_columns[] = $column;
      $this->_schema[$column] = $info;
      if (isset($info['key']) AND $info['key'] == 'primary') {
        $this->_primaryKey = $column;
      }
    }
  }

  /**
   * Add an index to schema
   * @param string $index Index name
   * @param string[] $columns An array of column names
   * @param bool $unique Whether or not index is unique
   */
  public function addIndex($index, $columns, $unique = false) {
    if (!$this->_readOnly) {
      if (!is_array($columns)) {
        $columns = array($columns);
      }
      if (isset($this->indexes[$index])) {
        $this->indexes[$index]['columns'] = array_merge(
          $this->indexes[$index]['columns'], $columns);
      }
      else {
        $this->indexes[$index] = array();
        $this->indexes[$index]['columns'] = $columns;
        if ($index == 'PRIMARY') {
          $unique = true;
        }
        $this->indexes[$index]['unique'] = $unique;
      }
    }
  }

  /**
   * Get column names
   * @return string[] Column names
   */
  public function getColumns() {
    return $this->_columns;
  }

  /**
   * Get name of primary key column
   * @return string Primary key column name
   */
  public function getPrimaryKey() {
    if (!isset($this->_primaryKey)) {
      $this->findPrimaryKey();
    }
    return $this->_primaryKey;
  }

  private function findPrimaryKey() {
    foreach ($this->_schema as $column => $info) {
      if (isset($info['key']) AND $info['key'] == 'primary') {
        $this->_primaryKey = $column;
        return;
      }
    }
  }

  /**
   * Export schema to PHP class
   * @param string $package Package (for documentation)
   * @param string $subpackage Subpackage (for documentation)
   * @return string PHP source
   */
  public function export($package = 'PeanutCMS', $subpackage = 'Schemas') {
    $source = '<?php' . PHP_EOL;
    $source .= '/**' . PHP_EOL;
    $source .= ' * Automatically generated schema for ' . $this->_name
        . ' table' . PHP_EOL;
    $source .= ' * @package ' . $package . PHP_EOL;
    $source .= ' * @subpackage ' . $subpackage . PHP_EOL;
    $source .= ' */' . PHP_EOL;
    $source .= 'class ' . $this->_name . 'Schema extends Schema {' . PHP_EOL;

    foreach ($this->_schema as $column => $info) {
      $source .= '  public $' . $column . ' = array(' . PHP_EOL;
      foreach ($info as $key => $value) {
        $source .= "    '" . $key . "' => " . var_export($value, true) . ','
            . PHP_EOL;
      }
      $source .= '  );' . PHP_EOL . PHP_EOL;
    }

    $source .= '  public $indexes = array(' . PHP_EOL;
    foreach ($this->indexes as $index => $info) {
      $source .= "    '" . $index . "' => array(" . PHP_EOL;
      $source .= "      'columns' => array('";
      $source .= implode("', '", $info['columns']);
      $source .= "')," . PHP_EOL;
      $source .= "      'unique' => ";
      $source .= var_export($info['unique'], true);
      $source .= PHP_EOL;
      $source .= '    ),' . PHP_EOL;
    }
    $source .= '  );' . PHP_EOL;
    $source .= '}' . PHP_EOL;
    return $source;
  }
}
