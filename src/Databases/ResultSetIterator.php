<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\Model;
use Jivoo\Models\IRecordIterator;
use Jivoo\Models\Selection\ReadSelection;

/**
 * Iterator for {@see IResultSet} instances.
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
   * @var ReadSelection Selection.
   */
  private $selection;

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
   * @param ReadSelection $selection The selection that created this result set.
   */
  public function __construct(Model $model, IResultSet $resultSet, ReadSelection $selection) {
    $this->model = $model;
    $this->selection = $selection;
    $this->resultSet = $resultSet;
    if ($this->resultSet->hasRows())
      $this->array[] = $this->model->createExisting($this->resultSet->fetchAssoc(), $selection);
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
      $this->array[] = $this->model->createExisting($this->resultSet->fetchAssoc(), $this->selection);
    $this->position++;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return isset($this->array[$this->position]);
  }
  
  /**
   * Convert result set to array.
   * @return \Jivoo\Databases\IRecord[] Array of records.
   */
  public function toArray() {
    while ($this->resultSet->hasRows())
      $this->next();
    return $this->array;
  }
}