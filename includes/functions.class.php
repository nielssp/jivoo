<?php
/*
 * Class for overwriting functions
 *
 * @package PeanutCMS
 */

/**
 * Functions class
 */
class Functions {

  /**
   * Contains overwritable functions
   * @var array
   */
  var $functions;

  /**
   * Constructor
   */
  function Functions() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    $this->functions = array();
  }

  /**
   * PHP5-style destructor
   *
   * @return bool true
   */
  function __destruct() {
    return true;
  }

  /**
   * Register a function
   *
   * @param string $name Function name
   * @param callback $function Function name
   */
  function register($name, $function) {
    if (!is_callable($function))
      return;
    $this->functions[$name] = $function;
  }

  function exists($name) {
    return isset($this->functions[$name]);
  }

  /**
   *
   * @param string $name
   * @param callback $function
   */
  function unregister($name) {
    if (isset($this->functions[$name]))
      unset($this->functions[$name]);
  }

  /**
   * Return output of registered function
   *
   * @param string $name Function name
   * @param mixed $,... Additional parameters
   */
  function call($name) {
    if (!isset($this->functions[$name]) OR !is_callable($this->functions[$name]))
      return;
    $numArgs = func_num_args();
    $args = array();
    if ($numArgs > 1) {
      $args = func_get_args();
      array_shift($args);
    }
    return call_user_func_array($this->functions[$name], $args);
  }

}