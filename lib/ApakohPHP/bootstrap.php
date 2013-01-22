<?php
ini_set('magic_quotes_runtime', 0);

if (!defined('LIB_PATH')) {
  define('LIB_PATH', str_replace('\\', '/', dirname(dirname(__FILE__))));
}

if (!defined('APAKOH_LIB_PATH')) {
  define('APAKOH_LIB_PATH', str_replace('\\', '/', dirname(__FILE__)));
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
        preg_match(
          '/([a-zA-Z0-9\_]+)::' . $call['function'] . '/',
        $line,
        $matches
        );
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

require APAKOH_LIB_PATH . '/Lib.php';
require APAKOH_LIB_PATH . '/ErrorReporting.php';

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

Lib::import('*');
