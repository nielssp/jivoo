<?php


interface ISelectable {

  public static function getById($id);

  public static function select(Selector $selector = NULL);

}

class Selector extends BaseObject {
  protected $orderBy;
  protected $descending;
  protected $limit;
  protected $where;
  protected $offset;

  /* Properties begin */
  protected $_getters = array('orderBy', 'descending', 'limit', 'where', 'offset');
  protected $_setters = array();

  private function _get_ascending() {
    return !$this->descending;
  }
  /* Properties end */

  private function __construct() {
    $this->limit = -1;
    $this->offset = 0;
    $this->where = array();
    $this->orderBy = 'id';
    $this->descending = false;
  }

  public static function create() {
    return new self();
  }

  public function limit($limit) {
    $this->limit = $limit;
    return $this;
  }

  public function offset($offset) {
    $this->offset = $offset;
    return $this;
  }

  public function where($column, $value) {
    $this->where[$column] = $value;
    return $this;
  }

  public function orderBy($column) {
    $this->orderBy = $column;
    return $this;
  }

  public function desc() {
    $this->descending = true;
    return $this;
  }

  public function asc() {
    $this->descending = false;
    return $this;
  }
}