<?php
/**
 * Contains data for a single record belonging to a model.
 * @package Jivoo\Models
 */
interface IBasicRecord extends ArrayAccess {
  /**
   * Get value of a field.
   * @param string $field Field name.
   * @return mixed Value.
   * @throws InvalidPropertyException If the field does not exist.
   */
  public function __get($field);
  
  /**
   * Determine if a field is set.
   * @param string $field Field name.
   * @return bool True if not null, false otherwise.
   * @throws InvalidPropertyException If the field does not exist.
   */
  public function __isset($field);
  
  /**
   * Get all data as an associative array.
   * @return array Array of data.
   */
  public function getData();

  /** 
   * Get associated model.
   * @return IBasicModel Associated model.
   */
  public function getModel();
  
  /**
   * Get associative array of field names and error messages. 
   * @return string[] Associative array of field names and error messages.
   */
  public function getErrors();
  
  /**
   * Whether or not the record contains errors.
   * @return bool True if record is considered valid (i.e. no errors).
   */
  public function isValid();
}
