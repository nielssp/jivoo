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
  protected $relations;

  /* Properties begin */
  protected $_getters = array('orderBy', 'descending', 'limit', 'where', 'offset', 'relations');
  protected $_setters = array();

  private function _get_ascending() {
    return !$this->descending;
  }
  /* Properties end */

  private function __construct() {
    $this->limit = -1;
    $this->offset = 0;
    $this->where = array();
    $this->relations = array();
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

  public function relation($table, $id) {
    $this->relations[$table][] = $id;
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

class SelectHelper extends BaseObject {

  private $className;
  private $tableName;

  protected $defaultSelector;

  protected $_getters = array(
    'defaultSelector'
  );

  protected $_setters = array(
    'defaultSelector'
  );

  public function __construct($className, $tableName) {
    $this->className = $className;
    $this->tableName = $tableName;
    $this->defaultSelector = Selector::create();
  }

  public function select(Selector $selector) {
    global $PEANUT;
    if (!isset($selector)) {
      $selector = $this->defaultSelector;
    }
    if ($selector->orderBy != 'id') {
      $index = $PEANUT['flatfiles']->getIndex($this->tableName, $selector->orderBy);
    }

    if ($selector->descending) {
      arsort($index);
    }
    else {
      asort($index);
    }
    reset($index);
    $all = array();
    $i = 0;
    foreach ($index as $id => $date) {
      if ($i < $selector->offset) {
        $i++;
        continue;
      }
      if ($selector->limit != -1
      AND ($i - $selector->offset) >= $selector->limit) {
        break;
      }
      $get = call_user_func(array($this->className, 'getById'), $id);
      $add = true;
      foreach ($selector->where as $column => $value) {
        if ($get->$column != $value) {
          $add = false;
          break;
        }
      }
      if ($add) {
        $all[] = $get;
        $i++;
      }
    }
    reset($all);
    return $all;
  }
}

