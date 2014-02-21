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
   * Find a record by it's primary key
   * @param mixed $primaryKey Value of primary key
   * @param mixed ...$primaryKeys
   * @return IRecord
   */
  public function find($primaryKey);
  
  /**
   * @param array $data
   * @param string[]|null $allowedFields
   * @return IRecord
  */
  public function create($data = array(), $allowedFields = null);
  /**
   * @param IRecord|array|array[] $data
   * @return IModel
  */
  public function insert($data);
}