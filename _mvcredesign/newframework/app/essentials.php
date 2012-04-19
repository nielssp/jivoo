<?php
/**
 * PeanutCMS core functions and aliases
 *
 * Functions and aliases to make things easier
 *
 * @package PeanutCMS
 * @since 0.2.0
 */

/** The PeanutCMS essentials has been included */
define('PEANUTCMS', TRUE);

require_once('constants.php');

/**
 * Translate function alias
 *
 * %n, where n is a number, is used as placeholder for the additional arguments
 *
 * @param string $text Text to translate
 * @param string $args,... Additional OPTIONAL arguments
 * @return string Translated text
 */
function tr($text) {
  $service = TranslationService::getService();
  $args = func_get_args();
  if ($service) {
    return call_user_func_array(array($service, 'translate'), $args);
  }
  else {
    $numArgs = func_num_args();
    if ($numArgs > 1) {
      for ($i = 1; $i < $numArgs; $i++) {
        $text = str_replace('%'.$i, $args[$i], $text);
      }
    }
    return $text;
  }
}

/**
 * Translate function to create listings
 *
 * Works somewhat like implode, but more with more options.
 * E.g. the sentence "The classes 'errors', 'i18n', and 'configuration' are missing from PeanutCMS" would be translated with:
 *
 * <code>
 * trl("The class '%l' is missing from %1.", "The classes '%l' are missing from %1.", "', '", "', and '", array('errors', 'i18n', 'configuration'), 'PeanutCMS');
 * </code>
 *
 * @param string $single Text to translate if there are only one piece in array
 * @param string $plural Text to translate if there are 0 or more than one pieces in array
 * @param string $glue String to put between pieces in array
 * @param string $gluell String to put between the last two pieces in the array
 * @param array $pieces The array with pieces
 * @param string $args,... Additional OPTIONAL arguments
 * @return string Translated text
 */
function trl($single, $plural, $glue, $gluel, $pieces) {
  $service = TranslationService::getService();
  $args = func_get_args();
  if ($service) {
    return call_user_func_array(array($service, 'translateList'), $args);
  }
  else {
    if (count($pieces) == 1) {
      $text = $single;
    }
    else {
      $text = $plural;
    }

    $list = '';
    for ($i = 0; $i < count($pieces); $i++) {
      $list .= $pieces[$i];
      if ($i != (count($pieces) - 1)) {
        if ($i == (count($pieces) - 2)) {
          $list .= $gluel;
        }
        else {
          $list .= $glue;
        }
      }
    }
    $text = str_replace('%l', $list, $text);

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 5; $i < $numArgs; $i++) {
        $n = $i-4;
        $text = str_replace('%'.$n, $args[$i], $text);
      }
    }
    return $text;
  }
}

/**
 * Translate function for numbers
 *
 * Used like tr() the placeholder for the number is %1 the optional arguments starts with number %2
 *
 * @param string $single Text to translate if %1 is 1
 * @param string $plural Text to translate if %1 is 0 or greater than 1
 * @param int $number The number to insert with the placeholder %1
 * @param string $args,... Additional OPTIONAL arguments starting with %2
 * @return string Translated text
 */
function trn($single, $plural, $number) {
  $service = TranslationService::getService();
  $args = func_get_args();
  if ($service) {
    return call_user_func_array(array($service, 'translateNumeral'), $args);
  }
  else {
    if ((int)$number == 1) {
      $text = $single;
    }
    else {
      $text = $plural;
    }

    $numArgs = func_num_args();
    if ($numArgs > 1) {
      $args = func_get_args();
      for ($i = 2; $i < $numArgs; $i++) {
        $n = $i-1;
        $text = str_replace('%'.$n, $args[$i], $text);
      }
    }
    return $text;
  }
}

function fdate($timestamp = NULL) {
  $service = TranslationService::getService();
  $args = func_get_args();
  if ($service) {
    return call_user_func_array(array($service, 'fdate'), $args);
  }
  else {
    return date('Y-m-d', $timestamp);
  }
}

function ftime($timestamp = NULL) {
  $service = TranslationService::getService();
  $args = func_get_args();
  if ($service) {
    return call_user_func_array(array($service, 'ftime'), $args);
  }
  else {
    return date('H:m:i', $timestamp);
  }
}

function tdate($format, $timestamp = NULL) {
  $service = TranslationService::getService();
  $args = func_get_args();
  if ($service) {
    return call_user_func_array(array($service, 'date'), $args);
  }
  else {
    return date($format, $timestamp);
  }
}

/**
 * Comparison function for use with usort() and uasort()
 *
 * @param array $a
 * @param array $b
 */
function prioritySorter($a, $b) {
  if ($a['priority'] < $b['priority']) {
    return 1;
  }
  if ($a['priority'] > $b['priority']) {
    return -1;
  }
  return 0;
}

/**
* Check if a string is a serialized array (!)
*
* This function will only check
*
* @param string $str String
* @return bool True if string is serialized
*/
function isSerialized($str){
  if (!is_string($str))
  return false;
  if (trim($str) == "")
  return false;
  if (preg_match('/^(i|s|a|o|d):(.*);/si', $str) == 0)
  return false;
  return true;
}

function classFileName($className) {
  $fileName = preg_replace('/([A-Z])/', '-$1', lcfirst($className));
  return strtolower($fileName);
}

function fileClassName($fileName) {
  $words = explode('-', $fileName);
  $className = '';
  foreach ($words as $word) {
    $className .= ucfirst($word);
  }
  return $className;
}

function precondition($condition) {
  if ($condition === TRUE) {
    return TRUE;
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

function p($relative) {
  if ($relative[0] == '/') {
    return $relative;
  }
  else {
    return PATH . $relative;
  }
}

function w($relative) {
  if ($relative[0] == '/') {
    return str_replace(
      rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'),
      '',
      $relative
    );
  }
  else {
    return WEBPATH . $relative;
  }
}

function __autoload($className) {
  if ($className[0] == 'I' AND file_exists($path = p(INTERFACES . classFileName(substr($className, 1)) . '.interface.php'))) {
    include($path);
  }
  else {
    $fileName = classFileName($className);
    if (file_exists(p(CLASSES . $fileName . '.class.php'))) {
      include(p(CLASSES . $fileName . '.class.php'));
    }
    else {
      include(p(MODULES . $fileName . '.class.php'));
    }
  }
}

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

if (!function_exists('lcfirst')) {
  function lcfirst($str) {
    $str[0] = strtolower($str[0]);
    return $str;
  }
}
