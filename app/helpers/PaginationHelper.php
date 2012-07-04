<?php

class PaginationHelper extends ApplicationHelper {
  
  private $limit = 5;
  
  private $count = 0;
  
  private $pages = 0;
  
  private $page = 1;
  
  public function setCount($count) {
    $this->count = $count;
  }
  
  public function setLimit($limit) {
    $this->limit = $limit;
  }
  
  public function paginate(SelectQuery $select) {    
    $this->pages = ceil($this->count / $this->limit);
    $select->limit($this->limit);
    if (isset($this->request->query['page'])) {
      $this->page = (int) $this->request->query['page'];
      $this->page = min($this->page, $this->pages);
      $this->page = max($this->page, 1);
    }
    $offset = ($this->page - 1) * $this->limit;
    $select->offset($offset);
  }
  
  public function getPage() {
    return $this->page;
  }
  
  public function getPages() {
    return $this->pages;
  }
  
  public function isFirst() {
    return $this->page == 1;
  }

  public function isLast() {
    return $this->page == $this->pages;
  }
  
  public function prevLink() {
    return $this->getLink(array('query' => array('page' => $this->page - 1)));
  }
  
  public function nextLink() {
    return $this->getLink(array('query' => array('page' => $this->page + 1)));
  }
}
