<?php
/*
 * Class for attaching functions to fiters
 *
 * @package PeanutCMS
 */

/**
 * Filters class
 */
class Filters {

  /**
   * Contains filters
   * @var array
   */
  private $filters;
  /**
   * PHP5-style constructor
   */
  public function __construct() {
    $this->filters = array();
  }

  /**
   * Add a function to a filter
   *
   * @todo Add priority parameter
   * @param string $filter Filter name
   * @param callback $function Function name
   */
  public function add($filter, $function, $priority = 5) {
    if (!is_callable($function))
      return;
    $this->filters[$filter][] = $function;
  }

  /**
   *
   * @param string $filter
   * @param callback $function
   */
  public function remove($filter, $function = null) {
    if (!isset($this->filters[$filter]) OR !is_array($this->filters[$filter]))
      return;
    if (is_null($function)) {
      unset($this->filters[$filter]);
    }
    elseif (($key = array_search($function, $this->filters[$filter])) !== false) {
      unset($this->filters[$filter][$key]);
    }
  }

  /**
   * Apply a filter to a variable
   *
   * @param string $filter Filter name
   * @param mixed $,... Additional parameters
   */
  public function apply($filter, $variable) {
    if (!isset($this->filters[$filter]) OR !is_array($this->filters[$filter]))
      return;
    $numArgs = func_num_args();
    $args = array();
    if ($numArgs > 1) {
      $args = func_get_args();
      array_shift($args);
      array_shift($args);
    }
    foreach ($this->filters[$filter] as $function) {
      $variable = call_user_func_array($function, array_merge(array($variable), $args));
    }
    return $variable;
  }

}