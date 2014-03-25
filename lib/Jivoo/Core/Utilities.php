<?php
/**
 * Useful functions
 * 
 * @package Core
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

  public static function camelCaseToUnderscores($camelCase) {
    $underscores = preg_replace('/([A-Z])/', '_$1', lcfirst($camelCase));
    return strtolower($underscores);
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
    foreach ($words as $word)
      $camelCase .= ucfirst($word);
    return $camelCase;
  }

  public static function underscoresToCamelCase($underscores) {
    $words = explode('_', $underscores);
    $camleCase = '';
    foreach ($words as $word)
      $camelCase .= ucfirst($word);
    return $camelCase;
  }
  
  /**
   * Get plural form of word
   * @param string $word Word
   * @return string Plural
   */
  public static function getPlural($word) {
    return $word . 's';
  } 
  
  /**
   * Check if a character is uppercase
   * @TODO Unicode support?
   * @param string $char A single ascii character
   * @return boolean True if uppercase, false otherwise
   */
  public static function isUpper($char) {
    $ascii = ord($char);
    return $ascii >= 65 AND $ascii <= 90;
  } 
  
  /**
   * Check if a character is lowercase
   * @TODO Unicode support?
   * @param string $char A single ascii character
   * @return boolean True if lowercase, false otherwise
   */
  public static function isLower($char) {
    $ascii = ord($char);
    return $ascii >= 97 AND $ascii <= 122;
  }

  /**
   * Returns the portion of string specified by the start and length parameters.
   * @param string $string
   * @param int $start
   * @param int $length
   * @return string Returns the extracted part of string; or FALSE on failure, or an empty string.
   */
  public static function substr($string, $start, $length = NULL) {
    if (function_exists('mb_substr')) {
      return mb_substr($string, $start, $length, 'UTF-8');
    }
    else {
      return join('', array_slice(
        preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY), $start, $length
      ));
    }
  }
  
  /**
   * Test a condition and throw an exception if it's false 
   * @param boolean $condition Condition
   * @throws InvalidArgumentException When condition is false
   */
  public static function precondition($condition) {
    if ($condition === true) {
      return;
    }
    $bt = debug_backtrace();
    $call = $bt[0];
    $lines = file($call['file']);
    preg_match('/' . $call['function'] . '\((.+)\)/',
      $lines[$call['line'] - 1], $matches);
    throw new InvalidArgumentException(
      'Precondition not met (' . $matches[1] . ').');
  }
  
  /**
   * Get lower case file extension from file name
   * @param string $file File name
   * @return string File extension
   */
  public static function getFileExtension($file) {
    $array = explode('.', $file);
    return strtolower(array_pop($array));
  }

  /**
   * Get content-type (MIME type) of a file name or file extension
   * @param string $fileName File name or extension
   * @return string Content type, 'text/plain' if unknown
   */
  public static function getContentType($fileName) {
    $fileExt = strtolower($fileName);
    if (strpos($fileExt, '.')) {
      $segments = explode('.', $fileExt);
      $fileExt = $segments[count($segments) - 1];
    }
    switch ($fileExt) {
      case 'htm':
        $fileExt = 'html';
      case 'css':
      case 'html':
        return 'text/' . $fileExt;
      case 'js':
        return 'application/javascript';
      case 'json':
        return 'application/json';
      case 'rss':
        return 'application/rss+xml';
      case 'atom':
        return 'application/atom+xml';
      case 'xhtml':
        return 'application/xhtml+xml';
      case 'xml':
        return 'application/xml';
      case 'jpg':
        $fileExt = 'jpeg';
      case 'gif':
      case 'jpeg':
      case 'png':
        return 'image/' . $fileExt;
      default:
        return 'text/plain';
    }
  }

  public static function getExtension($mimeType) {
    switch ($mimeType) {
      case 'text/html':
        return 'html';
      case 'text/plain':
        return 'text';
      case 'text/css':
        return 'css';
      case 'text/javascript':
      case 'application/javascript':
        return 'js';
      case 'application/json':
        return 'json';
      case 'application/rss+xml':
        return 'rss';
      case 'application/atom+xml':
        return 'atom';
      case 'application/xhtml+xml':
        return 'xhtml';
      case 'application/xml':
        return 'xml';
      case 'image/jpeg':
        return 'jpeg';
      case 'image/png':
        return 'png';
      case 'image/gif':
        return 'gif';
    }
    return null;
  }

  /**
   * Sort an array of IGroupables
   * @param IGroupable[] $objects An array of IGroupables
   * @return boolean True if successful, false if empty array or not an array  
   */
  public static function groupObjects(&$objects) {
    if (!is_array($objects) OR count($objects) < 1) {
      return false;
    }
    uasort($objects, array('Utilities', 'groupSorter'));
    return true;
  }

  /**
   * Compare two IGroupable-objects
   * @param IGroupable $a First
   * @param IGroupable $b Second
   * @return int Difference
   */
  public static function groupSorter(IGroupable $a, IGroupable $b) {
    $groupA = $a->getGroup();
    $groupB = $b->getGroup();
    if (is_numeric($groupA) AND is_numeric($groupB)) {
      return $groupA - $groupB;
    }
    else {
      return strcmp($groupA, $groupB);
    }
  }
  
  /**
   * Comparison function for use with usort() and uasort()
   * @depracted Is this still used anywhere?
   * @param array $a First
   * @param array $b Second
   * @return int difference
   */
  public static function prioritySorter($a, $b) {
    return $b['priority'] - $a['priority'];
  }
}
