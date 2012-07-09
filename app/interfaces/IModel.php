<?php

interface IModel {
  public function __get($field);
  public function __set($field, $value);
  public function __isset($field);
  public function getFields();
  public function getFieldType($field);
}
