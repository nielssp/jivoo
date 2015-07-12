<?php
header('Content-Type: text/plain');
include '../lib/Jivoo/Core/bootstrap.php';

Lib::import('Jivoo/Models');
Lib::import('Jivoo/Models/Validation');

abstract class Enum {
  private static $values = array();
  
  private final function __construct() { }
  
  public static function getValues($class) {
    if (!isset(self::$values[$class])) {
      if (!is_subclass_of($class, 'Enum'))
        throw new InvalidEnumException(tr('Enum type "%1" must extend class "%2"', $class, 'Enum'));
      $ref = new ReflectionClass($class);
      self::$values[$class] = array_flip($ref->getConstants());
      if (count(self::$values[$class]) < 1)
        throw new InvalidEnumException(tr('Enum type "%1" must contain at least one constant', $class));
    }
    return self::$values[$class];
  }
}

class InvalidEnumException extends Exception { }

class MyEnum extends Enum {
  const foo = 1;
  const bar = 2;
  const baz = 3;
}

$type = DataType::enum('MyEnum');

var_dump($type);
