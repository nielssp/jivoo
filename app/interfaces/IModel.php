<?php

interface IModel {
  public function __get($field);
  public function __set($field, $value);
  public function __isset($field);
  public function addData($data);
  public function getName();
  public function getFields();
  public function getFieldType($field);
  public function getFieldLabel($field);
  public function isFieldRequired($field);
  public function isField($field);
  public function isValid();
  public function getErrors();
  public function encode($field, $for = 'html', $options = array());
}
