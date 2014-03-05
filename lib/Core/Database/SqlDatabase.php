<?php
/**
 * A generic SQL database
 * @package Core\Database
 */
abstract class SqlDatabase extends MigratableDatabase implements ISqlDatabase {
  /**
   * @var string Table prefix
   */
  protected $tablePrefix = '';

  private $typeAdapter = null;
  
  /**
   * @var array Associative array of table names and {@see SqlTable} objects
   */
  protected $tables = array();

  /**
   * Destructor
   */
  function __destruct() {
    $this->close();
  }
  
  public function __get($name) {
    $table = $this->tableName($name);
    if (!isset($this->tables[$table]))
      throw new TableNotFoundException(tr(
        'Table "%1" does not exist.', $name
      ));
    return $this->tables[$table];
  }

  public function __isset($name) {
    return isset($this->tables[$this->tableName($name)]);
  }

  protected function setTypeAdapter(IMigrationTypeAdapter $typeAdapter) {
    $this->typeAdapter = $typeAdapter;
  }

  public function getTable($name, ISchema $schema) {
    $table = $this->tableName($name);
    if (!isset($this->tables[$table])) {
      $this->tables[$table] = new SqlTable($this, $name, $schema);
    }
    return $this->tables[$table];
  }

  public function tableName($name) {
    return $this->tablePrefix . Utilities::camelCaseToUnderscores($name);
  }

//   protected function tableCreated($name) {
//     $table = $this->tableName($name);
//     $this->tables[$table] = new SqlTable($this, $name);
//   }
  
  /**
   * Initialise table objects based on a result set
   * @param IResultSet $result Result of e.g. a SHOW query on MySQL
   */
  protected function initTables(IResultSet $result) {
    $prefixLength = strlen($this->tablePrefix);
    while ($row = $result->fetchRow()) {
      $name = $row[0];
      if (substr($name, 0, $prefixLength) == $this->tablePrefix) {
//        $name = substr($name, $prefixLength);
//         $this->tables[$name] = true;
      }
    }
  }

  /**
   * Escape a string and surround with quotation marks
   * @param string $string String
   */
  public abstract function quoteString($string);
  
  private $vars;
  private $varCount;

  private function replaceVar($matches) {
    $value = $this->vars[$this->varCount];
    $this->varCount++;
    if (isset($matches[3])) {
      $type = DataType::fromPlaceholder($matches[3]);
      $value = $this->typeAdapter->encode($type, $value);
    }
    return $this->quoteString($value);
  }
  
  /**
   * Escape a query
   * @param string $format Query format, use question marks '?' instead of values
   * @param mixed[] $vars List of values to replace question marks with
   * @return string The escaped query
   */
  public function escapeQuery($format, $vars) {
    $sqlString = '';
    $key = 0;
    $chars = str_split($format);
    if (!is_array($vars)) {
      $vars = func_get_args();
      array_shift($vars);
    }
    $this->vars = $vars;
    $this->varCount = 0;
    return preg_replace_callback('/((\?)|%([istbfdan]))/', array($this, 'replaceVar'), $format);
    foreach ($chars as $offset => $char) {
      if ($char == '?'
          AND (!isset($chars[$offset - 1]) OR $chars[$offset - 1] != '\\')) {
        if (is_array($vars[$key]) AND isset($vars[$key]['table'])) {
          $sqlString .= $this->tableName($vars[$key]['table']);
        }
        else if (is_int($vars[$key])) {
          $sqlString .= (int) $vars[$key];
        }
        else if (is_float($vars[$key])) {
          $sqlString .= (float) $vars[$key];
        }
        else if ($vars[$key] === true) {
          $sqlString .= '1';
        }
        else if ($vars[$key] === false) {
          $sqlString .= '0';
        }
        else {
          $sqlString .= $this->quoteString($vars[$key]);
        }
        $key++;
      }
      else if ($char != '\\' OR !isset($chars[$offset + 1])
          OR $chars[$offset + 1] != '?') {
        $sqlString .= $char;
      }
    }
    return $sqlString;
  }

  public function getTypeAdapter() {
    return $this->typeAdapter;
  }

  public function checkSchema($table, ISchema $schema) {
    return $this->typeAdapter->checkSchema($table, $schema);
  }

  public function tableExists($table) {
    return $this->typeAdapter->tableExists($table);
  }

  public function createTable(Schema $schema) {
    $this->typeAdapter->createTable($schema);
  }

  public function dropTable($table) {
    $this->typeAdapter->dropTable($table);
  }

  public function addColumn($table, $column, DataType $type) {
    $this->typeAdapter->addColumn($table, $column, $type);
  }

  public function deleteColumn($table, $column) {
    $this->typeAdapter->deleteColumn($table, $column);
  }

  public function alterColumn($table, $column, DataType $type) {
    $this->typeAdapter->alterColumn($table, $column, $type);
  }

  public function createIndex($table, $index, $options = array()) {
    $this->typeAdapter->createIndex($table, $index, $options);
  }

  public function deleteIndex($table, $index) {
    $this->typeAdapter->deleteIndex($table, $index);
  }

  public function alterIndex($table, $index, $options = array()) {
    $this->typeAdapter->alterIndex($table, $index, $options);
  }
}

