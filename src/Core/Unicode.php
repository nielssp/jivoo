<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Utility functions for Unicode encoded strings. 
 */
class Unicode {
  private function __construct() { }

  /**
   * Check if a character is uppercase
   * @param string $char A single character.
   * @return bool True if uppercase, false otherwise.
   */
  public static function isUpper($char) {
    if (function_exists('mb_strtoupper'))
      return mb_strtoupper($char, 'UTF-8') == $char;
    return strtoupper($char) == $char;
  }

  /**
   * Check if a character is lowercase.
   * @param string $char A single character.
   * @return bool True if lowercase, false otherwise.
   */
  public static function isLower($char) {
    if (function_exists('mb_strtolower'))
      return mb_strtolower($char, 'UTF-8') == $char;
    return strtolower($char) == $char;
  }
  
  /**
   * Get the character length of a string.
   * @param string $string String.
   * @return int Number of bytes in string.
   */
  public static function length($string) {
    if (function_exists('mb_strlen'))
      return mb_strlen($string, 'UTF-8');
    return count(preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY));
  }
  
  /**
   * Returns the portion of string specified by the start and length parameters.
   * @param string $string String.
   * @param int $start Offset to start at.
   * @param int $length Optional length of slice.
   * @return string Slice.
   */
  public static function slice($string, $start, $length = null) {
    if (function_exists('mb_substr'))
      return mb_substr($string, $start, $length, 'UTF-8');
    return implode('', array_slice(
      preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY), $start, $length
    ));
  }
  
  /**
   * Whether a string starts with another string.
   * @param string $string String to test.
   * @param string $start String to compare start of $string with.
   * @return bool True if $string starts with $start.
   */
  public static function startsWith($string, $start) {
    return strncmp($string, $start, strlen($start)) === 0;
  }

  /**
   * Whether a string end with another string.
   * @param string $string String to test.
   * @param string $end String to compare end of $string with.
   * @return bool True if $string ends with $end.
   */
  public static function endsWith($string, $end) {
    $l = strlen($end);
    if ($l == 0)
      return true;
    return substr($string, -$l) === $end;
  }
}
