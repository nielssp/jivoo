<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers;

use Jivoo\Models\Selection\IReadSelection;

/**
 * Create pagination on an array or a {@see IReadSelection}.
 */
class PaginationHelper extends Helper {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('View', 'Routing');

  /**
   * @var int Number of items per page.
   */
  private $limit = 5;

  /**
   * @var int Total number of items.
   */
  private $count = 0;

  /**
   * @var int Total number of pages.
   */
  private $pages = 1;

  /**
   * @var int Current page.
   */
  private $page = 1;

  /**
   * @var int Current item offset.
   */
  private $offset = 0;
  
  /**
   * @var int First item.
   */
  private $from = null;

  /**
   * @var int Last item.
   */
  private $to = null;

  /**
   * Paginate a selection (using {@see IReadSelection::limit()} and
   * {@see IReadSelection::offset()}) or an array (using {@see array_slice()}).
   * @param IReadSelection|array $select Selection or array.
   * @param int $itemsPerPage Number of items per page.
   * @return IReadSelection|array Modified selection or sliced array.
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

  /**
   * Get total number of items.
   * @return int Number of items.
   */
  public function getCount() {
    return $this->count;
  }

  /**
   * Get start of page.
   * @return int Start of page.
   */
  public function getFrom() {
    return min($this->offset + 1, $this->count);
  }

  /**
   * Get end of page.
   * @return int End of page.
   */
  public function getTo() {
    return min($this->offset + $this->limit, $this->count);
  }

  /**
   * Get current page..
   * @return int Current page.
   */
  public function getPage() {
    return $this->page;
  }

  /**
   * Get number of pages.
   * @return int Number of pages.
   */
  public function getPages() {
    return $this->pages;
  }

  /**
   * Whether or not the current page is the first page.
   * @return boolean True if first page, false otherwise.
   */
  public function isFirst() {
    return $this->page == 1;
  }

  /**
   * Whether or not the current page is the last page.
   * @return boolean True if last page, false otherwise.
   */
  public function isLast() {
    return $this->page == $this->pages;
  }
  
  /**
   * Create a list of pages, useful for page selectors.
   * @param int $middle Number of pages around the current page.
   * @param int $start Number of pages at the start.
   * @param int $end Number of pages at the end.
   * @return int[] List of pages.
   */
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
  
  /**
   * Link to a page.
   * @param int $page Page.
   * @param string $fragment Fragment.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function link($page, $fragment = null) {
    return array(
      'query' => array('page' => $page),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  /**
   * Link to the previous page.
   * @param string $fragment Fragment.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function prevLink($fragment = null) {
    return array(
      'query' => array('page' => $this->page - 1),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  /**
   * Link to the next page.
   * @param string $fragment Fragment.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function nextLink($fragment = null) {
    return array(
      'query' => array('page' => $this->page + 1),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  /**
   * Link to the first page.
   * @param string $fragment Fragment.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function firstLink($fragment = null) {
    return array(
      'query' => array('page' => 1),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }

  /**
   * Link to the last page.
   * @param string $fragment Fragment.
   * @return array|ILinkable|string|null $route A route, see {@see Routing}.
   */
  public function lastLink($fragment = null) {
    return array(
      'query' => array('page' => $this->pages),
      'fragment' => $fragment,
      'mergeQuery' => true
    );
  }
}
