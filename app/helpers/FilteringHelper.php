<?php

class FilteringHelper extends ApplicationHelper {

  private $searchColumns = array();
  private $filterColumns = array();
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
    $where = '';
    $this->query = $this->request->query['filter']; 
    $words = explode(' ', $this->query);
    $filters = array();
    if (count($this->filterColumns) > 0) {
      foreach ($words as $key => $value) {
        $pos = strpos($value, ':');
        if ($pos !== FALSE) {
          $column = strtolower(substr($value, 0, $pos));
          if (isset($this->filterColumns[$column])) {
            unset($words[$key]);
            $filterValue = substr($value, $pos + 1);
            $filters[] = $column . ' = ?';
            $query->addVar($filterValue);
          }
        }
      }
    }
    if (count($filters) > 0) {
      $where .= '(' . implode(' OR ', $filters) . ')';
    }
    if (count($this->searchColumns) > 0) {
      $searchQuery = '%' . implode(' ', $words) . '%';
      if ($searchQuery != '%%') {
        if ($where != '') {
          $where .= ' AND ';
        }
        $searches = array();
        foreach ($this->searchColumns as $column => $bool) {
          $searches[] = $column . ' LIKE ?';
          $query->addVar($searchQuery);
        }
        $where .= '(' . implode(' OR ', $searches) . ')';
      }
    }
    $query->where($where);
  }

}
