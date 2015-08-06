<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\AccessControl;

/**
 * Attempts to provide cryptographically secure randomness (if available).
 */
class Random {
  private function __construct() { }

  /**
   * @param int $n
   * @return string
   */
  private static function php7Bytes($n) {
    if (!function_exists('random_bytes'))
      return null;
    return random_bytes($n);
  }
  
  /**
   * @param int $n
   * @return string
   */
  private static function mcryptBytes($n) {
    if (!function_exists('mcrypt_create_iv') or !defined('MCRYPT_DEV_URANDOM '))
      return null;
    $bytes = mcrypt_create_iv($n, MCRYPT_DEV_URANDOM);
    if ($bytes === false)
      return null;
    return $bytes;
  }

  /**
   * @param int $n
   * @return string
   */
  private static function opensslBytes($n) {
    if (!function_exists('openssl_random_pseudo_bytes'))
      return null;
    $bytes = openssl_random_pseudo_bytes($n, $strong);
    if ($bytes === false)
      return null;
    return $bytes;
  }

  /**
   * @param int $n
   * @return string
   */
  private static function urandomBytes($n) {
    $f = fopen('/dev/urandom', 'r');
    if (!$f)
      return null;
    $bytes = '';
    $l = 0;
    do {
      $bytes .= fread($f, $n - $l);
      $l = self::length($bytes);
    } while ($l < $n);
    fclose($f);
    return $bytes;
  }
  
  /**
   * @param int $n
   * @return string
   */
  private static function mtRandBytes($n) {
    $bytes = '';
    for ($i = 0; $i < $n; $i++)
      $bytes .= chr(mt_rand(0, 255));
    return $bytes;
  }

  /**
   * Generate a random sequence of bytes.
   * @param int $n Number of bytes.
   * @param string $method Output parameter for the method used to generate
   * bytes: 'php7', 'mcrypt', 'openssl', 'urandom', or 'mt_rand'.
   * @return string String of bytes.
   */
  public static function bytes($n, &$method = null) {
    $bytes = self::php7Bytes($n);
    $method = 'php7';
    if (!isset($bytes)) {
      $bytes = self::mcryptBytes($n);
      $method = 'mcrypt';
    }
    if (!isset($bytes)) {
      $bytes = self::opensslBytes($n);
      $method = 'openssl';
    }
    if (!isset($bytes)) {
      $bytes = self::urandomBytes($n);
      $method = 'urandom';
    }
    if (!isset($bytes)) {
      $bytes = self::mtRandBytes($n);
      $method = 'mt_rand';
    }
    $l = self::length($bytes);
    if ($l < $n)
      $bytes .= self::mtRandBytes($n - $l);
    return $bytes;
  }
  
  /**
   * @param int $min
   * @param int $max
   * @return int
   */
  private static function php7Int($min, $max) {
    if (!function_exists('random_int'))
      return null;
    return random_int($min, $max);
  }

  /**
   * @param int $min
   * @param int $max
   * @return int
   */
  private static function bytesToInt($min, $max) {
    $int = hexdec(bin2hex(self::bytes(8))) - 1;
    return intval($int / 0xFFFFFFFFFFFFFFFF * ($max - $min + 1) + $min);
  }

  /**
   * @param int $min
   * @param int $max
   * @return int
   */
  private static function mtRandInt($min, $max) {
    $max = min($max, mt_getrandmax());
    return mt_rand($min, $max);
  }

  /**
   * Generate a random integer.
   * @param int $min Lowest value.
   * @param int $max Highest value.
   * @return int Random integer.
   */
  public static function int($min, $max) {
    $int = self::php7Int($min, $max);
    if (!isset($int))
      $int = self::bytesToInt($min, $max);
    return $int;
  }
  
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
   * Get the first $n bytes of a string (e.g. binary data).
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
}