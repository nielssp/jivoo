<?php
class ResultSetIterator implements Iterator {
  /**
   * @var IResultSet
   */
  private $resultSet;
  
  private $model;

  private $position = 0;
  private $array = array();

  public function __construct(IModel $model, IResultSet $resultSet) {
    $this->model = $model;
    $this->resultSet = $resultSet;
    if ($this->resultSet->hasRows())
      $this->array[] = ActiveRecord::createExisting($this->model, $this->resultSet->fetchAssoc());
  }

  function rewind() {
    $this->position = 0;
  }

  function current() {
    return $this->array[$this->position];
  }

  function key() {
    return $this->position;
  }

  function next() {
    if ($this->resultSet->hasRows())
      $this->array[] = ActiveRecord::createExisting($this->model, $this->resultSet->fetchAssoc());
    $this->position++;
  }

  function valid() {
    return isset($this->array[$this->position]);
  }
}