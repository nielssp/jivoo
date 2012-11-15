<?php
/**
 * Represents a data record containing
 * labeled fields.
 * @package PeanutCMS
 * @subpackage Interfaces
 */
interface IModel {
  /**
   * Get value of field
   * @param string $field Field name
   * @return mixed Value
   */
  public function __get($field);

  /**
   * Set value of field
   * @param string $field Field name
   * @param mixed $value Value
   */
  public function __set($field, $value);

  /**
   * Check if a field exists
   * @param string $field Field name
   * @return bool True if it does, false otherwise
   */
  public function __isset($field);

  /**
   * Add data to record
   * @param array $data An associative array containing key/value-pairs
   */
  public function addData($data);

  /**
   * Get name of record/model if applicable
   * @return string Name
   */
  public function getName();

  /**
   * Get a list of fields in record
   * @return string[] An array containing field names
   */
  public function getFields();

  /**
   * Get the type of a field
   * @param string $field Field name
   * @return string Field type
   */
  public function getFieldType($field);

  /**
   * Get the label of a field
   * @param string $field Field name
   * @return string Field label
   */
  public function getFieldLabel($field);

  /**
   * Get an editor associated with a field
   * @param string $field Field name
   * @return IEditor|null Editor if it exists, null otherwise
   */
  public function getFieldEditor($field);

  /**
   * Whether a field is required
   * @param string $field Field name
   * @return bool True if required, false otherwise
   */
  public function isFieldRequired($field);

  /**
   * Whether a field exists
   * @param string $field Field name
   * @return bool True if it does, false otherwise
   */
  public function isField($field);

  /**
   * Whether or not the data in the current record is valid
   * @return bool True if it is, false otherwise
   */
  public function isValid();

  /**
   * Get a list of errors in current record
   * @return array An associative array where the key is a field
   * and the value is an error string
   */
  public function getErrors();

  /**
   * Encode a field
   * @param string $field Field name
   * @param array $options Options for the encoder
   * @return string Encoded field value
   */
  public function encode($field, $options = array());
}
