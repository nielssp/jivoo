<?php
/**
 * Contains data for a single record belonging to a model
 * @package Core\Models
 */
interface IBasicRecord extends arrayaccess {
  /**
   * Get value of a field
   * @param string $field Field name
   * @return mixed Value
   * @throws InvalidRecordFieldException if the field does not exist
   */
  public function __get($field);
  
  /**
   * Determine if a field is set
   * @param string $field Field name
   * @return bool True if not null, false otherwise
   * @throws InvalidRecordFieldException if the field does not exist
   */
  public function __isset($field);

  /** @return IBasicModel Associated model */
  public function getModel();
  
  /** @return array[] Associative array of field names and errors */
  public function getErrors();
  
  /** @return bool True if record is considered valid (i.e. no errors) */
  public function isValid();
}

/** A field does not exist in the model associated with the record */
class InvalidRecordFieldException extends Exception { }
