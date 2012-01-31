<?php
/*
 * Class for attaching functions hooks
 *
 * @package PeanutCMS
 */

/**
 * Hooks class
 */
class Hooks {

  /**
   * Contains hooks and attached functions
   * @var array
   */
  var $hooks;

  /**
   * Constructor
   */
  function Hooks() {
    return $this->__construct();
  }

  /**
   * PHP5-style constructor
   */
  function __construct() {
    $this->hooks = array();
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
   * Attach a function to a hook
   *
   * @todo Add priority parameter
   * @param string $hook Hook name
   * @param callback $function Function name
   */
  function attach($hook, $function, $priority = 5) {
    if (!is_callable($function))
      return;
    $this->hooks[$hook][] = $function;
  }

  /**
   *
   * @param string $hook
   * @param callback $function
   */
  function remove($hook, $function = null) {
    if (!isset($this->hooks[$hook]) OR !is_array($this->hooks[$hook]))
      return;
    if (is_null($function)) {
      unset($this->hooks[$hook]);
    }
    elseif (($key = array_search($function, $this->hooks[$hook])) !== false) {
      unset($this->hooks[$hook][$key]);
    }
  }

  /**
   * Run all functions attached to a hook
   *
   * @param string $hook Hook name
   * @param mixed $,... Additional parameters
   */
  function run($hook) {
    if (!isset($this->hooks[$hook]) OR !is_array($this->hooks[$hook]))
      return;
    $numArgs = func_num_args();
    $args = array();
    if ($numArgs > 1) {
      $args = func_get_args();
      array_shift($args);
    }
    foreach ($this->hooks[$hook] as $function) {
      call_user_func_array($function, $args);
    }
  }

}