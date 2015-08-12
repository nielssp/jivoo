<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\InvalidClassException;
use Jivoo\Autoloader;

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
   * Get namespace part of a class name.
   * @param string|object $className Class or object, e.g. 'Jivoo\Core\Utilities'.
   * @return string Namespace, e.g. 'Jivoo\Core'.
   */
  public static function getNamespace($className) {
    if (is_object($className))
      $className = get_class($className);
    if (strpos($className, '\\') === false)
      return '';
    return preg_replace('/\\\\[^\\\\]+$/', '', $className);
  }
  
  /**
   * Get class name part of a qualified class name.
   * @param string|object $className Class or object, e.g. 'Jivoo\Core\Utilities'.
   * @return string Class name, e.g. 'Utilities'.
   */
  public static function getClassName($className) {
    if (is_object($className))
      $className = get_class($className);
    $className = array_slice(explode('\\', $className), -1);
    return $className[0];
  }
  
  /**
   * Check whether or not $class extends $parent, and throw an exception if
   * it does not.
   * @param string|object $class Class name or object.
   * @param string $parent Expected parent class of $class.
   * @throws InvalidClassException if $class does not extend $parent.
   */
  public static function assumeSubclassOf($class, $parent) {
    if (!is_subclass_of($class, $parent)) {
      if (is_object($class))
        $class = get_class($class);
      if ($class === $parent)
        return;
      throw new InvalidClassException(tr(
        'Class "%1" should extend "%2"', $class, $parent
      ));
    }
  } 
  
  /**
   * Check whether a directory exists or create it if it doesn't.
   * @param string $file File path.
   * @param bool $create Attempt to create directory if it doesn't exist.
   * @param bool $recursive Whether to recursively create parent directories
   * as well.
   * @param int $mode Directory permission, default is 0777.
   * @return bool True if directory exists.
   */
  public static function dirExists($file, $create = true, $recursive = true, $mode = 0777) {
    return is_dir($file) or ($create and mkdir($file, $mode, $recursive));
  }
  
  /**
   * Get lower case file extension from file name.
   * @param string $file File name.
   * @return string File extension.
   */
  public static function getFileExtension($file) {
    $array = explode('?', $file);
    $array = explode('.', $array[0]);
    return strtolower(array_pop($array));
  }
  
  /**
   * Whether a path is absolute, e.g. it starts with a slash. 
   * @param string $path Path.
   * @return bool True if absolute, false if relative.
   */
  public static function isAbsolutePath($path) {
    if (isset($path[0]) and ($path[0] == '/' or $path[0] == '\\'))
      return true;
    if (preg_match('/^[A-Za-z0-9]+:/', $path) === 1)
      return true;
    return false;
  }

  /**
   * Get content-type (MIME type) of a file name or file extension.
   * @param string $fileName File name or extension.
   * @return string Content type, 'text/plain' if unknown.
   */
  public static function getContentType($fileName) {
    $array = explode('?', $fileName);
    $array = explode('.', $array[0]);
    $fileExt = strtolower(array_pop($array));
    return self::convertType($fileExt);
  }
  
  /**
   * Convert file extension type (e.g. 'html', 'json', etc.) to the corresponding
   * MIME type. If the type contains a forward slash already, it is not covnerted. 
   * @param string $type Type or file extension.
   * @return string A valid MIME type, 'text/plain' if unknown.
   */
  public static function convertType($type) {
    if (strpos($type, '/') !== false)
      return $type;
    switch ($type) {
      case 'htm':
        $type = 'html';
      case 'css':
      case 'html':
        return 'text/' . $type;
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
        $type = 'jpeg';
      case 'gif':
      case 'jpeg':
      case 'png':
        return 'image/' . $type;
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
