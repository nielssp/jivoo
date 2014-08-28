<?php
/**
 * A generic SQL database
 * @package Jivoo\Database
 */
abstract class SqlDatabase extends LoadableDatabase implements ISqlDatabase {
  /**
   * @var string Table prefix
   */
  protected $tablePrefix = '';

  private $typeAdapter = null;
  
  /**
   * @var array Associative array of table names and {@see SqlTable} objects
   */
  protected $tables = array();
  
  private $tableNames = array();

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
    return isset($this->tableNames[$this->tableName($name)]);
  }
  
  protected function getMigrationAdapter() {
    return $this->typeAdapter;
  }
  
  protected function setTypeAdapter(IMigrationTypeAdapter $typeAdapter) {
    $this->typeAdapter = $typeAdapter;
  }

  public function getTable($name, ISchema $schema) {
    $table = $this->tableName($name);
    if (!isset($this->tables[$table])) {
      $this->tables[$table] = new SqlTable($this->app, $this, $name, $schema);
    }
    return $this->tables[$table];
  }

  public function tableName($name) {
    return $this->tablePrefix . Utilities::camelCaseToUnderscores($name);
  }

  public function quoteTableName($name) {
    return '`' . $this->tableName($name) . '`';
  }

  /**
   * Escape a string and surround with quotation marks
   * @param string $string String
   */
  public abstract function quoteString($string);
  
  private $vars;
  private $varCount;
  
  private function encodeValue(DataType $type = null, $value) {
    if (!isset($type))
      return $this->typeAdapter->encode(DataType::detectType($value), $value);
    return $this->typeAdapter->encode($type, $value);
  }

  private function replaceVar($matches) {
    $value = $this->vars[$this->varCount];
    $this->varCount++;
    $type = null;
    if (isset($matches[3]) and $matches[3] != '()')
      $type = DataType::fromPlaceholder($matches[3]);
    if (isset($matches[4]) or (isset($matches[3]) and $matches[3] == '()')) {
      assume(is_array($value));
      foreach ($value as $key => $v)
        $value[$key] = $this->encodeValue($type, $v);
      return '(' . implode(', ', $value) . ')';
    }
    return $this->encodeValue($type, $value);
  }
  
  private function replaceTable($matches) {
    return $this->quoteTableName($matches[1]);
  }
  
  /**
   * Escape a query
   * @param string $format Query format, use question marks '?' instead of values
   * @param mixed[] $vars List of values to replace question marks with
   * @return string The escaped query
   */
  public function escapeQuery($format, $vars = array()) {
    $sqlString = '';
    $key = 0;
    $chars = str_split($format);
    if (!is_array($vars)) {
      $vars = func_get_args();
      array_shift($vars);
    }
    $this->vars = $vars;
    $this->varCount = 0;
    $boolean = DataType::boolean();
    $true = $this->encodeValue($boolean, true);
    $false = $this->encodeValue($boolean, false);
    $format = preg_replace('/\btrue\b/i', $true, $format);
    $format = preg_replace('/\bfalse\b/i', $false, $format);
    $format = preg_replace_callback('/\{(.+?)\}/', array($this, 'replaceTable'), $format);
    return preg_replace_callback('/((\?)|%([a-z]+))(\(\))?/i', array($this, 'replaceVar'), $format);
  }

  public function getTypeAdapter() {
    return $this->typeAdapter;
  }

  public function tableExists($table) {
    return $this->typeAdapter->tableExists($table);
  }


  
  public function beginTransaction() {
    $this->rawQuery('BEGIN');
  }
  
  public function commit() {
    $this->rawQuery('COMMIT');
    
  }
  
  public function rollback() {
    $this->rawQuery('ROLLBACK');
  }
}

