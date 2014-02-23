<?php
class UpdateSelection extends BasicSelection implements IUpdateSelection {
  /**
   * @var array Associative array of column names and values
   */
  protected $sets = array();

  /**
   * Assign value to column, or if $value is null and $column is an array, then
   * assign multiple values to multiple columns.
   * @param string|array $column Column name or associative array of column
   * names and values
   * @param string $value Value
   * @return self Self
  */
  public function set($column, $value = null) {
    if (is_array($column)) {
      foreach ($column as $col => $val) {
        $this->set($col, $val);
      }
    }
    else {
      $this->sets[$column] = $value;
    }
    return $this;
  }

  public function update() {
    $this->model->update($this);
  }
}