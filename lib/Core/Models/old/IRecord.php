<?php
/**
 * Represents a record
 * @package Core\Models
 */
interface IRecord {
  /**
   * Get value of a $field
   * @param string $field Field name
   * @return mixed Value
   */
  public function __get($field);

  /**
   * Set value of a $field
   * @param string $field Field name
   * @param mixed $value Value
   */
  public function __set($field, $value);
  
  /**
   * Determine if field exists
   * @param string $field Field name
   * @return boolean True if it does, false otherwise
   */
  public function __isset($field);

  /**
   * Add data to record
   * @param array $data Data for record
   * @param string[] $allowedFields If set, only allow setting these fields
   */
  public function addData($data, $allowedFields = null);
  
  /**
   * Get model of record
   * @return IModel Model
   */
  public function getModel();
  
  /**
   * Save this record
   * @param array $options Associative array of options
   * @return boolean True if record saved successfully, false otherwise
   */
  public function save($options = array());
  
  /**
   * Delete this record
   */
  public function delete();
  
  /**
   * Check if record is new, i.e. it has not been saved yet
   * @return boolean True if it is, false otherwise
   */
  public function isNew();
  
  /**
   * Check if record has been saved, i.e. no changes has been made since the
   * last call to save().
   * @return boolean True if it is, false otherwise
   */
  public function isSaved();
  
  /**
   * Check if record data is valid
   * @return boolean True if it is, false otherwise
   */
  public function isValid();
  
  /**
   * Get a list of errors in this record
   * @return array An associative array where the keys are fields and the
   * values are translated error descriptions
   */
  public function getErrors();
  
  /**
   * Encode and return the value of a field, e.g. for display on an HTML
   * page
   * @param string $field Field name
   * @param array $options Associative array of options for encoder
   * @return mixed Encoded value
   */
  public function encode($field, $options = array());
}