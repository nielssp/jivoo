<?php
class ActiveRecord extends Record {

  private final function __construct(ActiveModel $model, $data = array(), $allowedFields = null) {
    $this->model = $model;
    $this->addData($data, $allowedFields);
  }

  public static function createNew(ActiveModel $model, $data = array(), $allowedFields = null, $class = null) {
    if (isset($class))
      $record = new $class($model, $data, $allowedFields);
    else
      $record = new ActiveRecord($model, $data, $allowedFields);
    $record->new = true;
    $record->saved = false;
    return $record;
  }
  
  public static function createExisting(ActiveModel $model, $data = array(), $class = null) {
    if (isset($class))
      $record = new $class($model, $data);
    else
      $record = new ActiveRecord($model, $data);
    $record->updatedData = array();
    return $record;
  }

}
