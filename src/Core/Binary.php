<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Utility functions for binary data, i.e. byte strings. 
 */
class Binary {
  private function __construct() { }
  
  /**
   * Get the byte length of a string (e.g. binary data).
   * @param string $string String.
   * @return int Number of bytes in string.
   */
  public static function length($string) {
    if (function_exists('mb_strlen'))
      return mb_strlen($string, '8bit');
    return strlen($string);
  }
  
  /**
   * Returns the portion of byte string specified by the start and length
   * parameters.
   * @param string $string String.
   * @param int $start Offset to start at.
   * @param int $length Optional length of slice.
   * @return string Slice.
   */
  public static function slice($string, $start, $length = null) {
    if (function_exists('mb_substr'))
      return mb_substr($string, $start, $length, '8bit');
    return substr($string, $start, $length);
  }
  
  /**
   * Encode binary data using base64.
   * @param string $data String.
   * @param bool $url 'base64url' standard. Removes padding and replaces '+'
   * and '/' with '-' and '_'.
   * @return string Base64 encoded string.  
   */
  public static function base64Encode($data, $url = false) {
    if ($url)
      return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    else
      return base64_encode($data);
  }
  
  /**
   * Decode base64 encoded string.
   * @param string $data Base64 encoded string..
   * @param bool $url Whether to replace '-' and '_' with '+' and '/'.
   * @return string Original string.
   */
  public static function base64Decode($data, $url = true) {
    if ($url)
      return base64_decode(strtr($data, '-_', '+/'));
    else
      return base64_decode($data);
  }
}
