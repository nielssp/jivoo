<?php
class FilterToken {
  const T_STRING = 1;
  const T_NOT = 2;
  const T_AND = 3;
  const T_OR = 4;
  const T_EQUALS = 5;
  const T_LPARENTHESIS = 6;
  const T_RPARENTHESIS = 7;

  public $type = 0;
  public $value = null;

  public function __construct($type, $value = null) {
    $this->type = $type;
    $this->value = $value;
  }

  public static function getType($type) {
    switch ($type) {
    	case FilterToken::T_STRING:
    	  return 'T_STRING';
    	case FilterToken::T_NOT:
    	  return 'T_NOT';
    	case FilterToken::T_AND:
    	  return 'T_AND';
    	case FilterToken::T_OR:
    	  return 'T_OR';
    	case FilterToken::T_EQUALS:
    	  return 'T_EQUALS';
    	case FilterToken::T_LPARENTHESIS:
    	  return 'T_LPARENTHESIS';
    	case FilterToken::T_RPARENTHESIS:
    	  return 'T_RPARENTHESIS';
    }
    return 'T_UNKNOWN';
  }
}