<?php

class FilterScanner {

  private $input = array();
  private $current = null;

  private static $reserved = '= ()!&|"';

  public function scan($input) {
    $this->input = str_split($input);
    $this->pop();
    $tokens = array();
    while (($token = $this->scanNext()) != null) {
      $tokens[] = $token;
    }
    return $tokens;
  }

  public static function getEqualsOperator() {
    return self::$reserved[0];
  }

  private function pop() {
    $this->current = array_shift($this->input);
    return $this->current;
  }

  private function isSpace() {
    return $this->current == ' ';
  }

  private function scanString() {
    $value = '';
    $this->pop();
    while ($this->current != '"') {
      if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        // Error: Missing " (ignore it)
        return new FilterToken(FilterToken::T_STRING, $value);
      }
      $value .= $this->current;
      $this->pop();
    }
    $this->pop();
    return new FilterToken(FilterToken::T_STRING, $value);
  }

  private function scanWord() {
    $value = '';
    while ($this->current != null AND strpos(self::$reserved, $this->current) === false) {
      if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        break;
      }
      $value .= $this->current;
      $this->pop();
    }
    switch ($value) {
    	case 'not':
    	  return new FilterToken(FilterToken::T_NOT);
    	case 'and':
    	  return new FilterToken(FilterToken::T_AND);
    	case 'or':
    	  return new FilterToken(FilterToken::T_OR);
    	case 'NOT':
    	  return new FilterToken(FilterToken::T_NOT);
    	case 'AND':
    	  return new FilterToken(FilterToken::T_AND);
    	case 'OR':
    	  return new FilterToken(FilterToken::T_OR);
    }
    return new FilterToken(FilterToken::T_STRING, $value);
  }

  private function scanNext() {
    while ($this->isSpace()) {
      $this->pop();
    }
    if ($this->current == null) {
      return null;
    }
    if ($this->current == '"') {
      return $this->scanString();
    }
    switch ($this->current) {
    	case self::$reserved[0]:
    	  $this->pop();
    	  return new FilterToken(FilterToken::T_EQUALS);
    	case '(':
    	  $this->pop();
    	  return new FilterToken(FilterToken::T_LPARENTHESIS);
    	case ')':
    	  $this->pop();
    	  return new FilterToken(FilterToken::T_RPARENTHESIS);
    	case '!':
    	  $this->pop();
    	  return new FilterToken(FilterToken::T_NOT);
    	case '&':
    	  $this->pop();
    	  return new FilterToken(FilterToken::T_AND);
    	case '|':
    	  $this->pop();
    	  return new FilterToken(FilterToken::T_OR);
    }
    return $this->scanWord();
  }
}