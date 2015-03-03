<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * JSON encoding and decoding.
 * @TODO Fallback when json php extension is missing.
 */
class Json {
  /**
   * @var bool|null True if PHP 5.4 or higher.
   */
  private static $hasPrettyPrinting = null;
  
  /**
   * Encode a value as JSON.
   * @param mixed $object Any object.
   * @return string JSON.
   */
  public static function encode($object) {
    return json_encode($object);
  }
  
  private static function printJson($object, $prefix = '') {
    if (is_array($object)) {
      if (empty($object))
        return '[]';
      if (array_diff_key($object, array_keys(array_keys($object)))) {
        $pairs = array();
        foreach ($object as $key => $value) {
          $pairs[] = $prefix . '  ' . self::encode($key)
            . ': ' . self::printJson($value, $prefix . '  ');
        }
        return '{' . PHP_EOL . implode(',' . PHP_EOL, $pairs) . PHP_EOL . $prefix . '}';
      }
      else {
        $elements = array();
        foreach ($object as $value) {
          $elements[] = $prefix . '  ' . self::printJson($value, $prefix . '  ');
        }
        return '[' . PHP_EOL . implode(',' . PHP_EOL, $elements) . PHP_EOL . $prefix . ']';
      }
    }
    else {
      return self::encode($object);
    }
  }
  
  /**
   * Pretty print a value as JSON.
   * @param mixed $object Any object.
   * @return string JSON.
   */
  public static function prettyPrint($object) {
    return self::printJson($object);
//     if (!isset(self::$hasPrettyPrinting))
//       self::$hasPrettyPrinting = version_compare(PHP_VERSION, '5.4', '>=');
//     if (self::$hasPrettyPrinting)
//       return json_encode($object, JSON_PRETTY_PRINT);
//     else
//       return self::printJson($object);
  }
  
  /**
   * Decode a JSON string.
   * @param string $json JSON.
   * @return mixed Decoded JSON.
   */
  public static function decode($json) {
    return json_decode($json, true);
  }
  
  /**
   * Decode a file as JSON.
   * @param string $file File path.
   * @return mixed Decoded JSON.
   */
  public static function decodeFile($file) {
    return self::decode(file_get_contents($file));
  }
}