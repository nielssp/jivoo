<?php
/**
 * A more advanced extension of {@see IBasicRecord}.
 */
interface IModel extends ISelection, IBasicModel {
  /**
   * Get shcmea of model.
   * @return ISchema Schema for model.
   */
  public function getSchema();

  /**
   * Retrieve primary key if it is an auto incrementing integer
   * @return string|null Name of primary key if there is
   * only a single primary key and it is auto incrementing,
   * otherwise null.
   */
  public function getAiPrimaryKey();

  /**
   * Get validator for model.
   * @return IValidator Validator for model.
   */
  public function getValidator();

  /**
   * Make a selection that selects a single record.
   * @param IRecord $record A record.
   * @return ISelection A selection.
  */
  public function selectRecord(IRecord $record);

  /**
   * Make a selection that selects everything except for a single record.
   * @param IRecord $record A record.
   * @return ISelection A selection.
   */
  public function selectNotRecord(IRecord $record);

  /**
   * Find a record by its primary key. If the primary key
   * consists of multiple fields, this function expects a
   * parameter for each field (in alphabetical order).
   * @param mixed $primary Value of primary key.
   * @param mixed ...$primary For multifield primary key.
   * @return IRecord|null A single matching record or null if it doesn't exist.
   */
  public function find($primary);
  
  /**
   * Create a record.
   * @param array $data Associative array of record data.
   * @param string[]|null $allowedFields List of allowed fields (null for all
   * fields allowed), fields that are not allowed (or not in the model) will be
   * ignored.
   * @return IRecord A record.
   */
  public function create($data = array(), $allowedFields = null);
  /**
   * Insert data directly into model.
   * @param array $data Associative array of record data.
   * @return int Last insert id.
   */
  public function insert($data);
}
