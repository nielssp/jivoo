<?php
interface IModel extends ISelection {
  public function getName();
  /**
   * @return Schema
  */
  public function getSchema();

  /**
   * @return Validator
  */
  public function getValidator();

  /**
   * @param IRecord $record
   * @return ISelection
  */
  public function selectRecord(IRecord $record);

  /**
   * Find a record by its primary key. If the primary key
   * consists of multiple fields, this function expects a
   * parameter for each field (in alphabetical order).
   * @param mixed $primary Value of primary key
   * @param mixed ...$primary
   * @return IRecord|null A single matching record or null if it doesn't exist
   */
  public function find($primary);
  
  /**
   * @param array $data
   * @param string[]|null $allowedFields
   * @return IRecord
  */
  public function create($data = array(), $allowedFields = null);
  /**
   * @param array $data
   * @return IModel
  */
  public function insert($data);
}
