<?php
abstract class Model implements IModel {
  private $iterator = null;

  public function create($data = array(), $allowedFields = null) {
    return ActiveRecord::createNew($this, $data, $allowedFields);
  }

  public function selectRecord(IRecord $record) {
    $primaryKey = $this->getSchema()->getPrimaryKey();
    $selection = $this;
    foreach ($primaryKey as $field) {
      $selection = $selection->where($field . ' = ?', $record->$field);
    }
    return $selection;
  }

  public abstract function update(UpdateSelection $selection = null);
  public abstract function delete(DeleteSelection $selection = null);
  public abstract function count(ReadSelection $selection = null);
  public abstract function first(ReadSelection $selection = null);
  public abstract function last(ReadSelection $selection = null);
  
  /**
   * Read custom schema
   * @param ReadSelection $selection
   * @return array[]
   */
  public abstract function readCustom(ReadSelection $selection); 

  /**
   * @param ReadSelection $selection
   * @return Iterator
  */
  public abstract function read(ReadSelection $selection);

  public function setSelection(ReadSelection $selection) {
    $this->iterator = $this->read($selection);
  }
  
  public function getValidator() {
    return null;
  }
  
  // ICondition implementation

  public function __call($method, $args) {
    switch ($method) {
      case 'and':
        return call_user_func_array(array(new Selection($this), 'andWhere'), $args);
      case 'or':
        return call_user_func_array(array(new Selection($this), 'orWhere'), $args);
    }
  }
  
  public function hasClauses() {
    return false;
  }
  
  public function where($clause) {
    $args = func_get_args();
    return call_user_func_array(array(new Selection($this), 'where'), $args);
  }
  
  public function andWhere($clause) {
    $args = func_get_args();
    return call_user_func_array(array(new Selection($this), 'andWhere'), $args);
  }
  
  public function orWhere($clause) {
    $args = func_get_args();
    return call_user_func_array(array(new Selection($this), 'orWhere'), $args);
  }
  
  // IBasicSelection implementation
  
  /**
   * Limit number of affected rows
   * @param int $limit Limit
   * @return self Self
   */
  public function limit($limit) {
    $selection = new Selection($this);
    return $selection->limit($limit);
  }

  /**
   * Order by a column in ascending order
   * @param string $column Column name
   * @return self Self
   */
  public function orderBy($column) {
    $selection = new Selection($this);
    return $selection->orderBy($column);
  }

  /**
   * Order by a column in descending order
   * @param string $column Column name
   * @return self Self
   */
  public function orderByDescending($column) {
    $selection = new Selection($this);
    return $selection->orderByDescending($column);
  }

  /**
   * Reverse order of all orderBy's
   * @return self Self
   */
  public function reverseOrder() {
    return $this;
  }
  
  // IUpdateSelection implementation

  public function set($column, $value = null) {
    $selection = new UpdateSelection($this);
    return $selection->set($column, $value);
  }

  // IReadSelection implementation
  
  public function select($expression, $alias = null) {
    $select = new ReadSelection($this);
    return $select->select($expression, $alias);
  }
  
  public function groupBy($columns, $condition = null) {
    $select = new ReadSelection($this);
    return $select->groupBy($columns, $condition);
  }
  
  public function innerJoin(IModel $other, $condition, $alias = null) {
    $select = new ReadSelection($this);
    return $select->innerJoin($other, $condition, $alias);
  }
  public function leftJoin(IModel $other, $condition, $alias = null) {
    $select = new ReadSelection($this);
    return $select->leftJoin($other, $condition, $alias);
  }
  public function rightJoin(IModel $other, $condition, $alias = null) {
    $select = new ReadSelection($this);
    return $select->rightJoin($other, $condition, $alias);
  }

  public function offset($offset) {
    $select = new ReadSelection($this);
    return $select->offset($offset);
  }
  
  // Iterator implementation

  public function rewind() {
    if (!isset($this->iterator)) {
      $this->setSelection(new ReadSelection($this));
    }
    return $this->iterator->rewind();
  }

  public function current() {
    if (!isset($this->iterator)) {
      $this->setSelection(new ReadSelection($this));
    }
    return $this->iterator->current();
  }

  public function key() {
    if (!isset($this->iterator)) {
      $this->setSelection(new ReadSelection($this));
    }
    return $this->iterator->key();
  }

  public function next() {
    if (!isset($this->iterator)) {
      $this->setSelection(new ReadSelection($this));
    }
    return $this->iterator->next();
  }

  public function valid() {
    if (!isset($this->iterator)) {
      $this->setSelection(new ReadSelection($this));
    }
    return $this->iterator->valid();
  }
}