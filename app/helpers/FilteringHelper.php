<?php

class FilteringHelper extends ApplicationHelper {

  private $predefined = array();
  private $searchColumns = array();
  private $filterColumns = array();
  private $callbacks = array();
  private $query = '';

  protected function init() {
    $this->addPredefined(tr('All'), '');
  }
  
  public function __get($property) {
    switch ($property) {
      case 'query':
        return $this->$property;
    }
  }
  
  public function getQuery() {
    return $this->query;
  }
  
  public function addPredefined($label, $filter) {
    $this->predefined[] = array(
      'label' => $label,
      'query' => array('filter' => $filter) 
    );
  }

  public function addSearchColumn($column) {
    $this->searchColumns[$column] = true;
  }

  public function addFilterColumn($column) {
    $this->filterColumns[$column] = true;
  }
  
  public function getPredefined() {
    return $this->predefined;
  }

  public function filter(ICondition $query) {
    if (!isset($this->request->query['filter'])) {
      return;
    }
    $where = new Condition();
    $this->query = $this->request->query['filter']; 
    $words = explode(' ', $this->query);
    if (count($this->filterColumns) > 0) {
      foreach ($words as $key => $value) {
        $pos = strpos($value, ':');
        if ($pos !== false) {
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
      for ($i = 0; $i < count($words); $i++) {
        $word = $words[$i];
        if ($word[0] == '"') {
          if ($word[strlen($word)-1] == '"') {
            $searchQuery = '%' . substr($word, 1, -1) . '%';
          }
          else {
            $searchQuery = '%' . substr($word, 1);
            for ($i = $i + 1; $i < count($words); $i++) {
              $word = $words[$i];
              $searchQuery .= ' ';
              if ($word[strlen($word)-1] == '"') {
                $searchQuery .= substr($word, 0, -1);
                break;
              }
              else {
                $searchQuery .= ' ' . $word;
              }
            }
            $searchQuery .= '%';
            var_dump($searchQuery);
          }
        }
        else {
          $searchQuery = '%' . $word . '%';
        }
        if ($searchQuery != '%%') {
          foreach ($this->searchColumns as $column => $bool) {
            $where->and($column . ' LIKE ?', $searchQuery);
          }
        }
      }
    }
    if ($where->hasClauses()) {
      $query->and($where);
    }
  }

}
