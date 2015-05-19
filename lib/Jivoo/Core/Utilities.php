<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Collection of useful utility functions.
 */
class Utilities {
  private function __construct() {
  }

  /**
   * Convert path from Windows-style to UNIX-style.
   * @param string $path Windows-style path.
   * @return string UNIX-style path.
   */
  public static function convertPath($path) {
    return str_replace('\\', '/', $path);
//     return str_replace('\\', '/', realpath($path));
  }

  /**
   * Convert a real path from Windows-style to UNIX-style. Uses
   * {@see realpath) to look up the path.
   * @param string $path Windows-style path.
   * @return string UNIX-style path.
   */
  public static function convertRealPath($path) {
    return str_replace('\\', '/', realpath($path));
  }
  
  /**
   * Convert a CamelCase class-name to a lowercase dash-separated name. E.g.
   * from "CamelCase" to "camel-case". Also known as "lisp-case"
   * @param string $camelCase A camel case string.
   * @return string Dash-separated string.
   */
  public static function camelCaseToDashes($camelCase) {
    $dashes = preg_replace('/([A-Z])/', '-$1', lcfirst($camelCase));
    return strtolower($dashes);
  }

  /**
   * Convert a CamelCase class-name to a lowercase underscore-separated name.
   * E.g. from "CamelCase" to "camel_case". Also known as "snake_case".
   * @param string $camelCase A camel case string.
   * @return string Uderscore-separated string.
   */
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

  /**
   * Convert a lowercase underscore-separated name to a camel case class-name.
   * E.g. from "camel_case" to "CamelCase".
   * @param string $underscores Underscores-separated string
   * @return string A camel case string
   */
  public static function underscoresToCamelCase($underscores) {
    $words = explode('_', $underscores);
    $camelCase = '';
    foreach ($words as $word)
      $camelCase .= ucfirst($word);
    return $camelCase;
  }
  
  /**
   * Create slug style string from any string.
   * @TODO Unicode support?
   * @param string $string String.
   * @return string Slug.
   */
  public static function stringToDashes($string) {
    return preg_replace('/ /', '-', preg_replace('/[^a-z -]/', '', strtolower($string)));
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
   * Generate a random number.
   * @param int $min Lowest value.
   * @param int $max Highest value.
   * @return int Random number.
   */
  public static function randomInt($min, $max) {
    return mt_rand($min, $max);
  }

  /**
   * Generate a random string.
   * @param int $length Length of random string.
   * @param string $allowedChars Allowed characters.
   * @return string Random string.
   */
  public static function randomString($length, $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
    $max = strlen($allowedChars) - 1;
    $string = '';
    for ($i = 0; $i < $length; $i++) {
      $string .= $allowedChars[Utilities::randomInt(0, $max)];
    }
    return $string;
  }
  
  /**
   * Returns the portion of string specified by the start and length parameters.
   * @param string $string String.
   * @param int $start Start index.
   * @param int $length Length of substring.
   * @param bool $mb Whether to use mb_substr if available.
   * @return string Returns the extracted part of string; or FALSE on failure, or an empty string.
   */
  public static function substr($string, $start, $length = null, $mb = true) {
    if ($mb and function_exists('mb_substr')) {
      return mb_substr($string, $start, $length, 'UTF-8');
    }
    else {
      return join('', array_slice(
        preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY), $start, $length
      ));
    }
  }
  
  /**
   * Get lower case file extension from file name.
   * @param string $file File name.
   * @return string File extension.
   */
  public static function getFileExtension($file) {
    $array = explode('.', $file);
    return strtolower(array_pop($array));
  }

  /**
   * Get content-type (MIME type) of a file name or file extension.
   * @param string $fileName File name or extension.
   * @return string Content type, 'text/plain' if unknown.
   */
  public static function getContentType($fileName) {
    $array = explode('.', $fileName);
    $fileExt = strtolower(array_pop($array));
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

  /**
   * Get file extension for a MIME type.
   * @param string $mimeType MIME type.
   * @return string|null File extension or null if unknown.
   */
  public static function getExtension($mimeType) {
    switch ($mimeType) {
      case 'text/html':
        return 'html';
      case 'text/plain':
        return 'txt';
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
   * Comparison function for use with usort() and uasort() to sort
   * associative arrays with a 'priority'-key.
   * @param array $a First.
   * @param array $b Second.
   * @return int Difference.
   */
  public static function prioritySorter($a, $b) {
    return $b['priority'] - $a['priority'];
  }
}
