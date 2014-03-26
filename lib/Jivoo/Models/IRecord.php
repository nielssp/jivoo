<?php
/**
 * Contains mutable data for a single record belonging to a model
 */
interface IRecord extends IBasicRecord {
  /**
   * Set value of a field
   * @param string $field Field name
   * @param mixed $value Value
   * @throws InvalidRecordFieldException if the field does not exist
   */
  public function __set($field, $value);
  
  /**
   * Set a field value to null
   * @param string $field Field name
   * @throws InvalidRecordFieldException if the field does not exist
   */
  public function __unset($field);

  /**
   * Set value of a field
   * @param string $field Field name
   * @param mixed $value Value
   * @return self Self
   * @throws InvalidRecordFieldException if the field does not exist
   */
  public function set($field, $value);

  /** @return IModel Associated model */
//   public function getModel();
  
  /**
   * Add data to record
   * @param array $data Associative array of field names and values
   * @param string[]|null $allowedFields List of allowed fields (null for all
   * fields allowed), fields that are not allowed (or not in the model) will be
   * ignored
   */
  public function addData($data, $allowedFields = null);
  
  /** Save record */
  public function save();
  
  /** Delete record */
  public function delete();
  
  /** Determine if the record is new (i.e. not yet saved) */
  public function isNew();
  
  /** Determine if the record has unsaved data */
  public function isSaved();
}
