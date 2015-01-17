<?php
/**
 * Iterator for {@see IResultSet} instances.
 * @package Jivoo\Databases
 */
class ResultSetIterator implements IRecordIterator {
  /**
   * @var IResultSet Result set.
   */
  private $resultSet;
  
  /**
   * @var Model Model.
   */
  private $model;

  /**
   * @var int Index.
   */
  private $position = 0;
  
  /**
   * @var IRecord[] Records.
   */
  private $array = array();

  /**
   * Construct iterator.
   * @param Model $model Model.
   * @param IResultSet $resultSet Result set.
   */
  public function __construct(Model $model, IResultSet $resultSet) {
    $this->model = $model;
    $this->resultSet = $resultSet;
    if ($this->resultSet->hasRows())
      $this->array[] = $this->model->createExisting($this->resultSet->fetchAssoc());
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->position = 0;
  }

  /**
   * Get current record.
   * @return IRecord A record.
   */
  public function current() {
    return $this->array[$this->position];
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    if ($this->resultSet->hasRows())
      $this->array[] = $this->model->createExisting($this->resultSet->fetchAssoc());
    $this->position++;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return isset($this->array[$this->position]);
  }
}