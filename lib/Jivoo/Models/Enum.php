<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * Used for creating enum types.
 * 
 * For instance:
 * <code>
 * class DayOfWeek extends Enum {
 *   const monday = 1;
 *   const tuesday = 2;
 *   const wednesday = 3;
 *   const thursday = 4;
 *   const friday = 5;
 *   const saturday = 6;
 *   const sunday = 7;
 * }
 * </code>
 * 
 */
abstract class Enum {
  /**
   * @var array Values of enums.
   */
  private static $values = array();

  private final function __construct() { }

  /**
   * Get values of an enum class.
   * @param string $class Class name.
   * @throws InvalidEnumException If the class does not contain constants.
   * @return string[] Enum values.
   */
  public static function getValues($class = null) {
    if (!isset($class))
      $class = get_called_class();
    if (!isset(self::$values[$class])) {
      Lib::assumeSubclassOf($class, 'Enum');
      $ref = new ReflectionClass($class);
      self::$values[$class] = array_flip($ref->getConstants());
      if (count(self::$values[$class]) < 1)
        throw new InvalidEnumException(tr('Enum type "%1" must contain at least one constant', $class));
    }
    return self::$values[$class];
  }

  /**
   * Get index of an enum value.
   * @param string $str Enum value.
   * @param string $class Class name.
   * @return int Index.
   */
  public static function getValue($str, $class = null) {
    if (!isset($class))
      $class = get_called_class();
    if (!isset(self::$values[$class]))
      self::getValues($class);
    return array_search($str, self::$values[$class]);
  }
}

/**
 * For invalid enums.
 */
class InvalidEnumException extends Exception { }
