<?php
interface IUpdateSelection extends IBasicSelection {
  /**
   * @param string|array $column
   * @param mixed|null $value
   * @return IUpdateSelection
   */
  public function set($column, $value = null);

  /**
   * @return int Number of updated records
  */
  public function update();
}