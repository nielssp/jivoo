<?php

// MISSING DEFAULT VALUE!

class Field {
  const UNSIGNED = 0x1;
  const AUTO_INCREMENT = 0x2;
  const NOT_NULL = 0x4;

  public static function integer($name, $flags = 0) {
    
  }
  
  public static function string($name, $length = 255, $flags = 0) {
    
  }
  
  public static function boolean($name, $flags = 0) {
    
  }
  
  public static function text($name, $flags = 0) {
    
  }
  
  public static function binary($name, $flags = 0) {
    
  }
  
  public static function float($name, $flags = 0) {
    
  }
  
  public static function date($name, $flags = 0) {
    
  }
  
  public static function dateTime($name, $flags = 0) {
    
  }
}

class Index {
  public static function primary($field) {
    
  }
  public static function unique($name, $field) {
    
  }
  public static function index($name, $field) {
    
  }
}

// example schema file:

return array(
  Field::integer('id', Field::UNSIGNED | Field::AUTO_INCREMENT | Field::NOT_NULL),
  Field::string('username', 255, Field::NOT_NULL),
  Field::string('password', 255, Field::NOT_NULL),
  Field::string('session', 255, Field::NOT_NULL),
  Field::integer('hue', Field::UNSIGNED | Field::NOT_NULL),
  Field::datetime('created_at'),
  Field::datetime('updated_at'),
  Index::primary('id'),
  Index::unqiue('username', 'username').
  Index::index('session', 'session')
);