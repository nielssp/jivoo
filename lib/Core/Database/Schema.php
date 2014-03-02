<?php
/**
 * Represents a database table schema
 * @package Core\Database
 */
class Schema implements ISchema {
  /**
   * @var string[] List of column names
   */
  private $fields = array();
  
  /**
   * @var bool Whether or not schema is read only
   */
  private $readOnly = false;
  
  /**
   * @var string Name of table
   */
  private $name = 'undefined';

  /**
   * @var array List of indexes
   */
  private $indexes = array();

  /**
   * Constructor
   * @param string $name Name of schema
  */
  public function __construct($name = null) {
    $className = get_class($this);
    if ($className != __CLASS__) {
      if (!isset($name)) {
        $name = preg_replace('/Schema$/', '', $className);
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
   * @return DataType Type of field
   */
  public function __get($field) {
    if (isset($this->fields[$field])) {
      return $this->fields[$field];
    }
  }

  /**
   * Whether or not a column exists in schema
   * @param string $column Column name
   * @return bool True if it does, false otherwise
   */
  public function __isset($field) {
    return isset($this->fields[$field]);
  }

  public function __set($field, DataType $type) {
    if (!$this->readOnly) {
      $this->fields[$field] = $type;
    }
  }

  public function addAutoIncrementId($id = 'id') {
    if (!$this->readOnly) {
      $this->$id = DataType::integer(DataType::AUTO_INCREMENT | DataType::UNSIGNED);
    }
  }

  public function addTimestamps($createdAt = 'createdAt', $updatedAt = 'updatedAt') {
    if (!$this->readOnly) {
      $this->$createdAt = DataType::dateTime();
      $this->$updatedAt = DataType::dateTime();
    }
  }

  public function getFields() {
    return array_keys($this->fields);
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
   * Add a field to schema
   * @param string $column Column name
   * @param array $info Column information
   */
  public function addField($name, DataType $type) {
    if (!$this->readOnly) {
      $this->fields[$name] = $type;
    }
  }

  /**
   * Set primary key
   * @param string|string[] $columns An array of column names or a single column
   * name
   * @param string $columns,... Additional column names (if $columns is a single
   * column name)
   */
  public function setPrimaryKey($columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) > 1) {
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
  
  /**
   * Get columns of primary key
   * @return string[] List of column names or empty array if no primary key
   */
  public function getPrimaryKey() {
    if (!isset($this->indexes['PRIMARY'])) {
      return array();
    }
    return $this->indexes['PRIMARY']['columns'];
  }
  
  /**
   * Check if the column is part of the primary key
   * @param string $column Column name
   * @return boolean True if part of primary key, false otherwise
   */
  public function isPrimaryKey($column) {
    if (!isset($this->indexes['PRIMARY'])) {
      return false;
    }
    return in_array($column, $this->indexes['PRIMARY']['columns']);
  }

  /**
   * Add a unique index to schema
   * @param string $index Index name
   * @param string|string[] $columns An array of column names or a single column
   * name
   * @param string $columns,... Additional column names (if $columns is a single
   * column name)
   */
  public function addUnique($name, $columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) > 2) {
        array_shift($params);
        $columns = $params;
      }
      else {
        $columns = array($columns);
      }
    }
    if (isset($this->indexes[$name])) {
      $this->indexes[$name]['columns'] = array_merge($this->indexes[$name]['columns'], $columns);
    }
    else {
      $this->indexes[$name] = array(
        'columns' => $columns,
        'unique' => true
      );
    }
  }

  /**
   * Add an index to schema
   * @param string $index Index name
   * @param string|string[] $columns An array of column names or a single column
   * name
   * @param string $columns,... Additional column names (if $columns is a single
   * column name)
   */
  public function addIndex($name, $columns) {
    if (!is_array($columns)) {
      $params = func_get_args();
      if (count($params) > 2) {
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
    if (isset($this->indexes[$name])) {
      $this->indexes[$name]['columns'] = array_merge($this->indexes[$name]['columns'], $columns);
    }
    else {
      $this->indexes[$name] = array(
        'columns' => $columns,
        'unique' => false
      );
    }
  }
  
  /**
   * Get indexes. The 'PRIMARY'-index is the primary key
   * 
   * The returned array is of the following format:
   * <code>
   * array(
   *   'indexname' => array(
   *     'columns' => array('columnname1', 'columnname2'),
   *     'unique' => true
   *   )
   * )
   * </code>
   * @return array Associative array of index names and info
   */
  public function getIndexes() {
    return $this->indexes;
  }
  
  /**
   * Check whether or not an index exists
   * @param string $name Index name
   */
  public function indexExists($name) {
    return isset($this->indexes[$name]);
  }
  
  /**
   * Get information about an index.
   * @param string $name Index name
   * @return array Associative array with two keys: 'columns' is a list of
   * column names and 'unique' is a boolean.
   */
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
