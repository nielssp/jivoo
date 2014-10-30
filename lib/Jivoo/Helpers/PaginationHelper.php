<?php

class PaginationHelper extends Helper {

  protected $modules = array('View', 'Routing');

  private $limit = 5;

  private $count = 0;

  private $pages = 1;

  private $page = 1;

  private $offset = 0;
  
  private $from = null;

  private $to = null;

  /**
   * 
   * @param IReadSelection|array $select
   * @param number $itemsPerPage
   * @return unknown
   */
  public function paginate($select, $itemsPerPage = 5) {
    assume($itemsPerPage > 0);
    $this->limit = $itemsPerPage;
    if ($select instanceof IReadSelection)
      $this->count = $select->count();
    else
      $this->count = count($select);
    $this->pages = max(ceil($this->count / $this->limit), 1);
    
    if (isset($this->request->query['page'])) {
      $this->page = (int) $this->request->query['page'];
      $this->page = min($this->page, $this->pages);
      $this->page = max($this->page, 1);
      $this->offset = ($this->page - 1) * $this->limit;
    }
    else if (isset($this->request->query['from'])
        AND isset($this->request->query['to'])) {
      $this->from = min(max($this->request->query['from'], 1), $this->count);
      $this->offset = $this->from - 1;
      $this->to = min(max($this->request->query['to'], 1), $this->count);
      $this->limit = $this->to - $this->from + 1;
    }
    if (!$this->isLast())
      $this->view->blocks->relation('next', null, $this->getLink($this->nextLink()));
    if (!$this->isFirst())
      $this->view->blocks->relation('prev', null, $this->getLink($this->prevLink()));
    if ($select instanceof IReadSelection) {
      $select = $select->limit($this->limit);
      $select = $select->offset($this->offset);
      return $select;
    }
    else {
      return array_slice($select, $this->offset, $this->limit);
    }
  }

  public function getCount() {
    return $this->count;
  }

  public function getFrom() {
    return min($this->offset + 1, $this->count);
  }

  public function getTo() {
    return min($this->offset + $this->limit, $this->count);
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
  
  public function getPageList($middle = 3, $start = 1, $end = 1) {
    $pages = array();
    for ($i = 1; $i <= $start and $i <= $this->pages; $i++) {
      $pages[] = $i;
    }
    $i = max($i, $this->page - $middle + min(ceil($middle / 2), $this->pages - $this->page));
    for ($j = 1; $j <= $middle and $i <= $this->pages; $j++, $i++) {
      $pages[] = $i;
    }
    $i = max($i, $this->pages - $end + 1);
    for ( ; $i <= $this->pages; $i++) {
      $pages[] = $i;
    }
    return $pages;
  }
  
  public function link($page, $fragment = null) {
    return array(
      'query' => array('page' => $page),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  public function prevLink($fragment = null) {
    return array(
      'query' => array('page' => $this->page - 1),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  public function nextLink($fragment = null) {
    return array(
      'query' => array('page' => $this->page + 1),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  public function firstLink($fragment = null) {
    return array(
      'query' => array('page' => 1),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  public function lastLink($fragment = null) {
    return array(
      'query' => array('page' => $this->pages),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }
}
