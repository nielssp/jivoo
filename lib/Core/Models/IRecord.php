<?php
interface IRecord extends IBasicRecord {
  public function __set($field, $value);
  /** @return IModel Associated model */
  public function getModel();
  public function addData($data, $allowedFields = null);
  public function set($field, $value);
  public function save();
  public function delete();
  public function isNew();
  public function isSaved();
}
