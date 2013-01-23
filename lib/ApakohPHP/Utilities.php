<?php
/**
 * Useful functions
 * 
 * @package ApakohPHP
 */
class Utilities {
  private function __construct() {
    
  }
  
  /**
   * Convert a CamelCase class-name to a lowercase dash-separated name. E.g.
   * from "CamelCase" to "camel-case".
   * @param string $camelCase A camel case string
   * @return string Dash-separated string
   */
  public static function camelCaseToDashes($camelCase) {
    $dashes = preg_replace('/([A-Z])/', '-$1', lcfirst($camelCase));
    return strtolower($dashes);
  }

  /**
   * Convert a lowercase dash-separated name to a camel case class-name. E.g.
   * from "camel-case" to "CamelCase".
   * @param string $dashes  Dash-separated string
   * @return string A camel case string
   */
  public static function dashesToCamelCase($dashes) {
    $words = explode('-', $dashes);
    $camelCase = '';
    foreach ($words as $word) {
      $camelCase .= ucfirst($word);
    }
    return $camelCase;
  }
  
  /**
   * Test a condition and throw an exception if it's false 
   * @param boolean $condition Condition
   * @throws InvalidArgumentException When condition is false
   */
  function precondition($condition) {
    if ($condition === true) {
      return;
    }
    $bt = debug_backtrace();
    $call = $bt[0];
    $lines = file($call['file']);
    preg_match(
      '/' . $call['function'] . '\((.+)\)/',
      $lines[$call['line'] - 1],
      $matches
    );
    throw new InvalidArgumentException('Precondition not met (' . $matches[1] . ').');
  }
}