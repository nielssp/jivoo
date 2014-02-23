<?php
interface IRecord {
  public function __get($field);
  public function __set($field, $value);
  public function __isset($field);
  public function getModel();
  public function addData($data, $allowedFields = null);
  public function set($field, $value);
  public function save();
  public function delete();
  public function getErrors();
  public function isValid();
  public function isNew();
  public function isSaved();
}