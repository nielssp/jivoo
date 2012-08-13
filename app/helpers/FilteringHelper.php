<?php

class FilteringHelper extends ApplicationHelper {

  private $searchColumns = array();
  private $filterColumns = array();
  private $callbacks = array();
  private $query = '';

  public function __get($property) {
    switch ($property) {
      case 'query':
        return $this->$property;
    }
  }

  public function addSearchColumn($column) {
    $this->searchColumns[$column] = TRUE;
  }

  public function addFilterColumn($column) {
    $this->filterColumns[$column] = TRUE;
  }

  public function filter(SelectQuery $query) {
    if (!isset($this->request->query['filter'])) {
      return;
    }
    $where = new Condition();
    $this->query = $this->request->query['filter']; 
    $words = explode(' ', $this->query);
    if (count($this->filterColumns) > 0) {
      foreach ($words as $key => $value) {
        $pos = strpos($value, ':');
        if ($pos !== FALSE) {
          $column = strtolower(substr($value, 0, $pos));
          if (isset($this->filterColumns[$column])) {
            unset($words[$key]);
            $filterValue = substr($value, $pos + 1);
            $where->or($column . ' = ?', $filterValue);
          }
        }
      }
    }
    if ($where->hasClauses()) {
      $query->where($where);
    }
    $where = new Condition();
    if (count($this->searchColumns) > 0) {
      foreach ($words as $word) {
        $searchQuery = '%' . $word . '%';
        if ($searchQuery != '%%') {
          foreach ($this->searchColumns as $column => $bool) {
            $where->or($column . ' LIKE ?', $searchQuery);
          }
        }
      }
    }
    if ($where->hasClauses()) {
      $query->and($where);
    }
  }

}
