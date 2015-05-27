<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models\Selection;

use Jivoo\Models\IModel;
use Jivoo\Models\IRecord;
use Jivoo\Models\Condition\Condition;
use Jivoo\Models\DataType;

/**
 * A read selection.
 * @property-read int $offset Offset.
 * @proeprty-read array[] $joins List of arrays describing joings.
 * @property-read array[] $fields List of arrays describing fields.
 * @property-read array[] $additionalFields List of arrays describing fields.
 */
class ReadSelection extends BasicSelection implements IReadSelection {

  /**
   * @var int Offset
   */
  protected $offset = 0;

  /**
   * List of arrays describing joins.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'source' => ..., // Data source to join with ({@see IDataSource})
   *   'type' => ..., // Type of join: 'INNER', 'RIGHT' or 'LEFT'
   *   'alias' => ..., // Alias for other data source (string|null)
   *   'condition' => ... // Join condition ({@see Condition})
   * );
   * </code>
   * @var array[]
   */
  protected $joins = array();

  /**
   * List of arrays describing columns.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'expression' => ..., // Expression (string)
   *   'alias' => ... // Alias (string|null)
   * )
   * </code>
   * @var array[]
  */
  protected $fields = array();

  /**
   * List of arrays describing columns.
   *
   * Each array is of the following format:
   * <code>
   * array(
   *   'expression' => ..., // Expression (string)
   *   'alias' => ... // Alias (string|null)
   * )
   * </code>
   * @var array[]
   */
  protected $additionalFields = array();

  /**
   * {@inheritdoc}
   */
  public function select($expression, $alias = null) {
    $this->fields = array();
    if (is_array($expression)) {
      foreach ($expression as $exp => $alias) {
        if (is_int($exp)) {
          $this->fields[] = array(
            'expression' => $alias,
            'alias' => null
          );
        }
        else {
          $this->fields[] = array(
            'expression' => $exp,
            'alias' => $alias
          );
        }
      }
    }
    else {
      $this->fields[] = array(
        'expression' => $expression,
        'alias' => $alias
      );
    }
    $result = $this->model->readCustom($this);
    $this->fields = array();
    return $result;
  }
  
  /**
   * {@inheritdoc}
   */
  public function with($field, $expression, DataType $type = null) {
    $this->additionalFields[$field] = array(
      'alias' => $field,
      'expression' => $expression,
      'type' => $type
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function groupBy($columns, $condition = null) {
    if (!is_array($columns)) {
      $columns = array($columns);
    }
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    if (isset($this->groupBy)) {
      $columns = array_merge($this->groupBy['columns'], $columns);
      $condition = where($this->groupBy['condition'])->and($condition);
    }
    $this->groupBy = array('columns' => $columns, 'condition' => $condition,);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function innerJoin(IModel $dataSource, $condition = null, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array(
      'source' => $dataSource,
      'type' => 'INNER',
      'alias' => $alias,
      'condition' => $condition
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function leftJoin(IModel $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'LEFT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function rightJoin(IModel $dataSource, $condition, $alias = null) {
    if (!($condition instanceof Condition)) {
      $condition = new Condition($condition);
    }
    $this->joins[] = array('source' => $dataSource, 'type' => 'RIGHT',
      'alias' => $alias, 'condition' => $condition
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function first() {
    return $this->model->firstSelection($this);
  }

  /**
   * {@inheritdoc}
   */
  public function last() {
    return $this->model->lastSelection($this);
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->model->countSelection($this);
  }
  
  /**
   * Find row number of a record in selection.
   * @param IRecord $record A record.
   * @return int Row number.
   */
  public function rowNumber(IRecord $record) {
    return $this->model->rowNumberSelection($this, $record);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $array = array();
    foreach ($this as $record)
      $array[] = $record;
    return $array;
  }

  /**
   * {@inheritdoc}
   */
  public function offset($offset) {
    $this->offset = (int) $offset;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function getIterator() {
    return $this->model->getIterator($this);
  }
}
