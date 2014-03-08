<?php

class PaginationHelper extends Helper {

  protected $modules = array('Templates', 'Routing');

  private $limit = 5;

  private $count = 0;

  private $pages = 1;

  private $page = 1;

  private $offset = 0;

  private $to = 1;

  public function setCount($count) {
    $this->count = $count;
    $this->pages = max(ceil($this->count / $this->limit), 1);
    return $this;
  }

  public function setLimit($limit) {
    Utilities::precondition($limit > 0);
    $this->limit = $limit;
    $this->pages = max(ceil($this->count / $this->limit), 1);
    return $this;
  }

  public function paginate(IReadSelection $select) {
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
    $select->limit($this->limit);
    $select->offset($this->offset);

    if (!$this->isLast())
      $this->m->Templates->view->resource('next', null, $this->getLink($this->nextLink()));
    if (!$this->isFirst())
      $this->m->Templates->view->resource('prev', null, $this->getLink($this->prevLink()));
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
