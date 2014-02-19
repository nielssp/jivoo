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