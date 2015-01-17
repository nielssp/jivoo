<?php
/**
 * A record validator.
 * @package Jivoo\Models\Validation
 */
interface IValidator {
  /**
   * Validate a record.
   * @param IRecord $record Record to validate.
   * @return string[] An associative array of field names and error messages (array should
   * be empty if record is valid).
   */
  public function validate(IRecord $record);
}
