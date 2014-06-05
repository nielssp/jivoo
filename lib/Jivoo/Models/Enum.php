<?php
abstract class Enum {
  private static $values = array();

  private final function __construct() { }

  public static function getValues($class) {
    if (!isset(self::$values[$class])) {
      Lib::assumeSubclassOf($class, 'Enum');
      $ref = new ReflectionClass($class);
      self::$values[$class] = array_flip($ref->getConstants());
      if (count(self::$values[$class]) < 1)
        throw new InvalidEnumException(tr('Enum type "%1" must contain at least one constant', $class));
    }
    return self::$values[$class];
  }
}

class InvalidEnumException extends Exception { }