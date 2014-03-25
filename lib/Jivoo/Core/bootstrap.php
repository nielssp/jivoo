<?php
/**
 * Jivoo bootstrap script
 * @package Jivoo\Core
 */
ini_set('magic_quotes_runtime', 0);

if (!defined('LIB_PATH')) {
  define('LIB_PATH', str_replace('\\', '/', dirname(dirname(dirname(__FILE__)))));
}

if (!defined('CORE_LIB_PATH')) {
  define('CORE_LIB_PATH', str_replace('\\', '/', dirname(__FILE__)));
}

// PHP 5.2 compatibility
if (!function_exists('get_called_class')) {
  /**
   * Gets the name of the class the static method is called in
   * @return boolean|string Returns the class name of false on failure.
   */
  function get_called_class() {
    $bt = debug_backtrace();
    $matches = array();
    foreach ($bt as $call) {
      if (!isset($call['class'])) {
        continue;
      }
      $lines = file($call['file']);
      for ($l = $call['line']; $l > 0; $l--) {
        $line = $lines[$l - 1];
        preg_match('/([a-zA-Z0-9\_]+)::' . $call['function'] . '/', $line,
          $matches);
        if (!empty($matches)) {
          break;
        }
      }
      if (!empty($matches)) {
        break;
      }
    }
    if (!isset($matches[1])) {
      return false;
    }
    if ($matches[1] == 'self' OR $matches[1] == 'parent') {
      $line = $call['line'] - 1;
      while ($line > 0 && strpos($lines[$line], 'class') === false) {
        $line--;
      }
      preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
    }
    return $matches[1];
  }
}

// PHP 5.2 compatibility
if (!function_exists('lcfirst')) {
  /**
   * Convert first character of string to lowercase
   * @param string $str Input
   * @return string Output
   */
  function lcfirst($str) {
    $str[0] = strtolower($str[0]);
    return $str;
  }
}

/**
 * Translate function alias
 * @see I18n::get()
 */
function tr($message) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'get'), $args);
}

/**
 * Translate function alias
 * @see I18n::getNumeric()
 */
function tn($message, $singular, $number) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'getNumeric'), $args);
}

/**
 * Encode string for HTML usage
 * @param string $string Input
 * @return string Input
 */
function h($string) {
  return htmlentities($string, ENT_COMPAT, 'UTF-8');
  //return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

/**
 * Alias for Condition constructor
 * @param ICondition|string Condition
 * @return Condition Condition object
 */
function where($condition) {
  return new Condition($condition);
}

/**
 * @see I18n::formatDate()
 */
function fdate($timestamp = null) {
  return I18n::formatDate($timestamp);
}

/**
 * @see I18n::formatTime()
 */
function ftime($timestamp = null) {
  return I18n::formatTime($timestamp);
}

/**
 * @see I18n::longDate()
 */
function ldate($timestamp = null) {
  return I18n::longDate($timestamp);
}

/**
 * @see I18n::shortDate()
 */
function sdate($timestamp = null) {
  return I18n::shortDate($timestamp);
}

/**
 * @see I18n::date()
 */
function tdate($format, $timestamp = null) {
  return I18n::date($format, $timestamp);
}

function assume($condition, $message = null) {
  if ($condition === true) {
    return;
  }
  if (isset($message))
    throw new InvalidArgumentException($message);
  $bt = debug_backtrace();
  $call = $bt[0];
  $lines = file($call['file']);
  preg_match(
    '/' . $call['function'] . '\((.+)\)/',
    $lines[$call['line'] - 1],
    $matches
  );
  throw new InvalidArgumentException(tr('Assumption failed: %1', $matches[1]));
}

if (!class_exists('InvalidArgumentException')) {
  class InvalidArgumentException extends Exception { }
}

class InvalidMethodException extends Exception { }
class InvalidPropertyException extends Exception { }

require CORE_LIB_PATH . '/Lib.php';
require CORE_LIB_PATH . '/ErrorReporting.php';

error_reporting(-1);
set_error_handler(array('ErrorReporting', 'handleError'));
set_exception_handler(array('ErrorReporting', 'handleException'));

if (function_exists('spl_autoload_register')) {
  spl_autoload_register(array('Lib', 'autoload'));
}
else {
  function __autoload($class) {
    Lib::autoload($class);
  }
}

Lib::import('Jivoo/Core');
