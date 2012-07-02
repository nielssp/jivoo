<?php
/**
 * @deprecated Since build 3670. Use events instead
 */
final class Hooks {
  private static $hooks = array();

  private function __construct() { }

  /**
   * Attach a function to a hook
   *
   * @todo Add priority parameter
   * @param string $hook Hook name
   * @param callback $function Function name
   */
  public static function attach($hook, $function, $priority = 5) {
    trigger_error('Use of Hooks is deprecated.', E_USER_DEPRECATED);
    if (!is_callable($function)) {
      throw new FunctionNotCallableException('The function is not callable');
    }
    self::$hooks[$hook][] = $function;
  }

  /**
   *
   * @param string $hook
   * @param callback $function
   */
  public static function remove($hook, $function = null) {
    trigger_error('Use of Hooks is deprecated.', E_USER_DEPRECATED);
    if (!isset(self::$hooks[$hook]) OR !is_array(self::$hooks[$hook])) {
      return false;
    }
    if (is_null($function)) {
      unset(self::$hooks[$hook]);
    }
    elseif (($key = array_search($function, self::$hooks[$hook])) !== false) {
      unset(self::$hooks[$hook][$key]);
    }
  }

  /**
   * Run all functions attached to a hook
   *
   * @param string $hook Hook name
   * @param mixed $,... Additional parameters
   */
  public static function run($hook) {
    trigger_error('Use of Hooks is deprecated.', E_USER_DEPRECATED);
    if (!isset(self::$hooks[$hook]) OR !is_array(self::$hooks[$hook])) {
      return;
    }
    $numArgs = func_num_args();
    $args = array();
    if ($numArgs > 1) {
      $args = func_get_args();
      array_shift($args);
    }
    foreach (self::$hooks[$hook] as $function) {
      call_user_func_array($function, $args);
    }
  }
}

class FunctionNotCallableException extends Exception { }
