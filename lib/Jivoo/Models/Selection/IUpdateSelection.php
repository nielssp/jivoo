<?php
/**
 * An update selection.
 */
interface IUpdateSelection extends IBasicSelection {
  /**
   * Assign value to field. If $field is an associative array, then multiple
   * fields are assigned. If $field contains an equals sign ('=') then $field
   * is used as the set expression.
   * @param string|array $field Field name or associative array of field names
   * and values
   * @param string $value Value
   * @return IUpdateSelection An update selection.
   */
  public function set($field, $value = null);

  /**
   * Execute updates.
   * @return int Number of updated records.
   */
  public function update();
}
