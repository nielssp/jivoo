<?php
/**
 * Jivoo bootstrap script.
 * @package Jivoo\Core
 */
ini_set('magic_quotes_runtime', 0);

if (!defined('LIB_PATH')) {
  define('LIB_PATH', str_replace('\\', '/', dirname(dirname(dirname(__FILE__)))));
}

if (!defined('CORE_LIB_PATH')) {
  define('CORE_LIB_PATH', str_replace('\\', '/', dirname(__FILE__)));
}

/**
 * Translate function alias.
 * @see I18n::get()
 */
function tr($message) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'get'), $args);
}

/**
 * Translate function alias.
 * @see I18n::getNumeric()
 */
function tn($message, $singular, $number) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'getNumeric'), $args);
}

/**
 * Encode string for HTML usage.
 * @param string $string Input.
 * @return string Input.
 */
function h($string) {
  return htmlentities($string, ENT_COMPAT, 'UTF-8');
  //return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

/**
 * Alias for Condition constructor.
 * @param ICondition|string Condition.
 * @return Condition Condition object.
 */
function where($condition) {
  $args = func_get_args();
  $condition = new Condition();
  call_user_func_array(array($condition, 'andWhere'), $args);
  return $condition;
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

/**
 * Precondition function that can be used to add additional constraints to
 * function parameters.
 * @param bool $condition Condition.
 * @param string $message Failure message.
 * @throws InvalidArgumentException If condition is false.
 */
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
  /**
   * Thrown by function if parameters are invalid.
   */
  class InvalidArgumentException extends Exception { }
}

/**
 * Thrown when a magic method is undefined.
 */
class InvalidMethodException extends Exception { }

/**
 * Thrown when a magic property is undefined.
 */
class InvalidPropertyException extends Exception { }

require CORE_LIB_PATH . '/Lib.php';
require CORE_LIB_PATH . '/ErrorReporting.php';

error_reporting(-1);
set_error_handler(array('ErrorReporting', 'handleError'));
set_exception_handler(array('ErrorReporting', 'handleException'));

spl_autoload_register(array('Lib', 'autoload'));

Lib::import('Jivoo/Core');
