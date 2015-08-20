<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

use Jivoo\InvalidArgumentException;
use Jivoo\InvalidClassException;
use Jivoo\InvalidTypeException;

/**
 * Utility methods for assumptions/preconditions. See also {@see \assume} for
 * preconditions of arbitrary expressions.
 */
class Assume {
  private function __construct() {}

  /**
   * Check whether or not $class extends $parent, and throw an exception if
   * it does not.
   * @param string|object $class Class name or object.
   * @param string $parent Expected parent class of $class.
   * @throws InvalidClassException If $class does not extend $parent.
   */
  public static function isSubclassOf($class, $parent) {
    if (!is_subclass_of($class, $parent)) {
      if (is_object($class))
        $class = get_class($class);
      self::isString($class);
      if ($class === $parent)
        return;
      throw new InvalidClassException(
        'Class ' . $class . ' should extend ' . $parent
      );
    }
  }

  /**
   * Check whether or not $object is an instance of $class.
   * @param mixed 
   * @param string $class Class name.
   * @throws InvalidTypeException If $object is not an instance of $class.
   */
  public static function isInstanceOf($object, $class) {
    if (!($object instanceof $class))
      self::typeError($value, 'an instance of ' . $class);
  }
  
  public static function isString($value) {
    if (!is_string($value))
      self::typeError($value, 'a string');
  }
  
  public static function isInt($value) {
    if (!is_int($value))
      self::typeError($value, 'an integer');
  }
  
  public static function isFloat($value) {
    if (!is_float($value))
      self::typeError($value, 'a float');
  }
  
  public static function isResource($value) {
    if (!is_resource($value))
      self::typeError($value, 'a resource');
  }
  
  public static function isObject($value) {
    if (!is_object($value))
      self::typeError($value, 'an object');
  }
  
  public static function isArray($value) {
    if (!is_array($value))
      self::typeError($value, 'an array');
  }
  
  public static function isBool($value) {
    if (!is_bool($value))
      self::typeError($value, 'a boolean');
  }
  
  public static function isNonEmpty($value) {
    if (!is_bool($value))
      self::typeError($value, 'a boolean');
  }
  
  private static function typeError($value, $expected) {
    throw new InvalidTypeException(
      'Value of type "' . self::getType($value) . '" must be ' . $expected
    );
  }
  
  private static function getType($value) {
    if (is_object($value))
      return get_class($value);
    if (is_resource($value))
      return get_resource_type($value);
    return gettype($value);
  }
}