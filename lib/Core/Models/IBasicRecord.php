<?php
interface IBasicRecord {
  public function __get($field);
  public function __isset($field);
  /** @return IBasicModel Associated model */
  public function getModel();
  public function getErrors();
  public function isValid();
}
