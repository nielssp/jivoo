<?php
/**
 * Represents a data model containing
 * labeled fields.
 * @package Jivoo\Models
 */
interface IModel {
  /**
   * @var Model field types
   */
  const TYPE_TEXT = 1,
        TYPE_STRING = 2,
        TYPE_INTEGER = 3,
        TYPE_BOOLEAN = 4,
        TYPE_FLOAT = 5,
        TYPE_BINARY = 6,
        TYPE_DATE = 7,
        TYPE_DATETIME = 8;
  
  /**
   * Create a new record of model
   * @param array $data Data for record
   * @param string[] $allowedFields If set, only allow setting these fields
   * @return IRecord New record
   */
  public function create($data = array(), $allowedFields = null);
  
  /**
   * Get name of model if applicable
   * @return string Name
   */
  public function getName();
  
  /**
   * Get a list of fields in model
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
   * Get all records associated with model matching an optional
   * query
   * @param SelectQuery $query Optional query to match.
   * @return IRecord[] Array of records
   */
  public function all(SelectQuery $query = null);
  
  /**
   * Get first record associated with model matching an optional
   * query
   * @param SelectQuery $query Optional query to match.
   * @return IRecord A single record
   */
  public function first(SelectQuery $query = null);
  
  /**
   * Get last record associated with model matching an optional
   * query
   * @param SelectQuery $query Optional query to match.
   * @return IRecord A single record
   */
  public function last(SelectQuery $query = null);
  
  /**
   * Get number of records associated with model
   * @param SelectQuery $query Optional query to match.
   * @return int Number of records
   */
  public function count(SelectQuery $query = null);
}
