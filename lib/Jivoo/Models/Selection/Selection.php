<?php
/**
 * An undecided selection. Will transform into a more specific selection based
 * on use.
 * @package Jivoo\Models\Selection
 */
class Selection extends BasicSelection implements ISelection {
  /**
   * Copy attributes into a basic selection.
   * @param BasicSelection $copy A basic selection.
   * @return BasicSelection A basic selection.
   */
  private function copyBasicAttr(BasicSelection $copy) {
    $copy->where = $this->where;
    $copy->limit = $this->limit;
    $copy->orderBy = $this->orderBy;
    return $copy;
  }

  /**
   * Convert to read selection.
   * @return ReadSelection A read selection.
   */
  public function toReadSelection() {
    return $this->copyBasicAttr(new ReadSelection($this->model));
  }

  /**
   * {@inheritdoc}
   */
  public function set($column, $value = null) {
    return $this->copyBasicAttr(new UpdateSelection($this->model))->set($column, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function update() {
    return $this->copyBasicAttr(new UpdateSelection($this->model))->update();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    return $this->copyBasicAttr(new DeleteSelection($this->model))->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function select($column, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->select($column, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function groupBy($columns, $condition = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->groupBy($columns, $condition);
  }

  /**
   * {@inheritdoc}
   */
  public function innerJoin(IModel $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->innerJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function leftJoin(IModel $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->leftJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function rightJoin(IModel $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->rightJoin($other, $condition, $alias);
  }

  /**
   * {@inheritdoc}
   */
  public function first() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->first();
  }

  /**
   * {@inheritdoc}
   */
  public function last() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->last();
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->count();
  }

  /**
   * Find row number of a record in selection.
   * @param IRecord $record A record
   * @return int Row number.
   */
  public function rowNumber(IRecord $record) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->rowNumber($record);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function offset($offset) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->offset($offset);
  }

  /**
   * {@inheritdoc}
   */
  function getIterator() {
    return $this->model->getIterator($this->copyBasicAttr(new ReadSelection($this->model)));
  }
}
