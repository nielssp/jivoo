<?php
/**
 * An update selection.
 * @property-read array $sets An associative array of field names and values.
 */
class UpdateSelection extends BasicSelection implements IUpdateSelection {
  /**
   * @var array Associative array of field names and values
   */
  protected $sets = array();

  /**
   * {@inheritdoc}
   */
  public function set($field, $value = null) {
    if (is_array($field)) {
      foreach ($field as $f => $val) {
        $this->set($f, $val);
      }
    }
    else {
      if (strpos($field, '=') !== false) {
        if (!is_array($value)) {
          $value = func_get_args();
          $value = array_slice($value, 1);
        }
      }
      $this->sets[$field] = $value;
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    $this->model->updateSelection($this);
  }
}
