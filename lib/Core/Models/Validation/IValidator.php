<?php
/**
 * A record validator
 * @package Core\Models\Validation
 */
interface IValidator {
  /**
   * Validate a record
   * @param IRecord $record Record to validate
   * @return array An associative array of field names and error messages (array should
   * be empty if record is valid).
   */
  public function validate(IRecord $record);
}
