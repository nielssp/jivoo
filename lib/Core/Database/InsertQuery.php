<?php
/**
 * A query for inserting rows into table
 * @package Core\Database
 * @property-read string[] $columns List of columns
 * @property-read mixed[] $values List of values
 */
class InsertQuery extends Query {

  /**
   * @var string[] List of columns
   */
  protected $columns = array();
  
  /**
   * @var mixed[] List of values
   */
  protected $values = array();

  /**
   * Add a column
   * @param string $column Column name
   * @return self Self
   */
  public function addColumn($column) {
    $this->columns[] = $column;
    return $this;
  }

  /**
   * Add multiple columns
   * @param string[] $columns List of column names
   * @return self Self
   */
  public function addColumns($columns) {
    if (!is_array($columns)) {
      $columns = func_get_args();
    }
    foreach ($columns as $column) {
      $this->addColumn($column);
    }
    return $this;
  }

  /**
   * Add a value
   * @param mixed $value Value
   * @return self Self
   */
  public function addValue($value) {
    $this->values[] = $value;
    return $this;
  }

  /**
   * Add multiple values
   * @param mixed[] $values List of values
   * @return self Self
   */
  public function addValues($values) {
    if (!is_array($values)) {
      $values = func_get_args();
    }
    foreach ($values as $value) {
      $this->addValue($value);
    }
    return $this;
  }

  /**
   * Add a column/value pair
   * @param string $column Column name
   * @param mixed $value Value
   * @return self Self
   */
  public function addPair($column, $value) {
    $this->addColumn($column);
    $this->addValue($value);
    return $this;
  }

  /**
   * Add multiple column/value pairs
   * @param array $pairs Associative array of column names and values
   * @return self Self
   */
  public function addPairs($pairs) {
    if (is_array($pairs)) {
      foreach ($pairs as $column => $value) {
        $this->addColumn($column);
        $this->addValue($value);
      }
    }
    return $this;
  }

}
