<?php
ini_set('magic_quotes_runtime', 0);

if (!defined('LIB_PATH')) {
  define('LIB_PATH', str_replace('\\', '/', dirname(dirname(__FILE__))));
}

if (!defined('CORE_LIB_PATH')) {
  define('CORE_LIB_PATH', str_replace('\\', '/', dirname(__FILE__)));
}

// PHP 5.2 compatibility
if (!function_exists('get_called_class')) {
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
  function lcfirst($str) {
    $str[0] = strtolower($str[0]);
    return $str;
  }
}

/**
 * Translate function alias
 * @see I18n::translate()
 */
function tr($text) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'translate'), $args);
}

/**
 * Translate function to create listings
 * @see I18n::translateList()
 */
function trl($single, $plural, $glue, $gluel, $pieces) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'translateList'), $args);
}

/**
 * Translate function for numbers
 * @see I18n::translateNumeral
 */
function trn($single, $plural, $number) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'translateNumeral'), $args);
}

function h($string) {
  return htmlentities($string, ENT_COMPAT, 'UTF-8');
  //return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

function fdate($timestamp = null) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'formatDate'), $args);
}

function ftime($timestamp = null) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'formatTime'), $args);
}

function tdate($format, $timestamp = null) {
  $args = func_get_args();
  return call_user_func_array(array('I18n', 'date'), $args);
}

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

Lib::import('');
