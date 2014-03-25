<?php
/**
 * A generic database query
 * @package Jivoo\Database
 * @property-read IDataSource $dataSource Data source to execute query on
 */
abstract class Query {
  /**
   * @var IDataSource Data source to execute query on
   */
  private $dataSource = null;

  /**
   * Constructor
   */
  public function __construct() {}

  /**
   * Get value of property
   * @param string $property Property name
   * @return mixed Value
   */
  public function __get($property) {
    if (isset($this->$property)) {
      return $this->$property;
    }
  }

  /**
   * Check if property is set
   * @param string $property Property name
   * @return bool True if set, false otherwise
   */
  public function __isset($property) {
    return isset($this->$property);
  }

  /**
   * Create query, can be used instead of constructor for chaining purposes
   * @return self A new query
   */
  public static function create() {
    $class = get_called_class();
    return new $class();
  }

  /**
   * Set data source to execute query on
   * @param IDataSource $dataSource Data source
   * @return self Self
   */
  public function setDataSource(IDataSource $dataSource) {
    $this->dataSource = $dataSource;
    return $this;
  }

  /**
   * Execute query
   * @throws Exception If no data source set
   * @return mixed Depends on type of query
   */
  public function execute() {
    if (isset($this->dataSource)) {
      if ($this instanceof InsertQuery) {
        return $this->dataSource
          ->insert($this);
      }
      else if ($this instanceof SelectQuery) {
        return $this->dataSource
          ->select($this);
      }
      else if ($this instanceof UpdateQuery) {
        return $this->dataSource
          ->update($this);
      }
      else if ($this instanceof DeleteQuery) {
        return $this->dataSource
          ->delete($this);
      }
    }
    else {
      throw new Exception('No data source to execute on');
    }
  }

}
