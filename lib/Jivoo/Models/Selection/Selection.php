<?php
class Selection extends BasicSelection implements ISelection {
  private function copyBasicAttr(BasicSelection $copy) {
    $copy->where = $this->where;
    $copy->limit = $this->limit;
    $copy->orderBy = $this->orderBy;
    return $copy;
  }

  public function toReadSelection() {
    return $this->copyBasicAttr(new ReadSelection($this->model));
  }

  public function set($column, $value = null) {
    return $this->copyBasicAttr(new UpdateSelection($this->model))->set($column, $value);
  }

  public function update() {
    return $this->copyBasicAttr(new UpdateSelection($this->model))->update();
  }

  public function delete() {
    return $this->copyBasicAttr(new DeleteSelection($this->model))->delete();
  }

  public function select($column, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->select($column, $alias);
  }

  public function selectAll() {
    return $this->copyBasicAttr(new ReadSelection($this->model));
  }

  public function groupBy($columns, $condition = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->groupBy($columns, $condition);
  }

  public function innerJoin(IModel $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->innerJoin($other, $condition, $alias);
  }
  public function leftJoin(IModel $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->leftJoin($other, $condition, $alias);
  }
  public function rightJoin(IModel $other, $condition, $alias = null) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->rightJoin($other, $condition, $alias);
  }

  public function first() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->first();
  }
  public function last() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->last();
  }

  public function count() {
    return $this->copyBasicAttr(new ReadSelection($this->model))->count();
  }
  public function offset($offset) {
    return $this->copyBasicAttr(new ReadSelection($this->model))->offset($offset);
  }

  function getIterator() {
    return $this->model->getIterator($this->copyBasicAttr(new ReadSelection($this->model)));
  }
}
