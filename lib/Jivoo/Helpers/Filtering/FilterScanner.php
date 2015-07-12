<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Helpers\Filtering;

/**
 * A scanner for filters.
 */
class FilterScanner {
  /**
   * @var string[] Input characters.
   */
  private $input = array();

  /**
   * @var string Current character.
   */
  private $current = null;

  /**
   * @var string Reserved characters.
   */
  private static $reserved = '= ()!<>&|"';
  
  /**
   * @var string[] Mapping of custom operators to built-in ones.
   */
  private $customOperators = array();

  /**
   * Add a custom operator.
   * @param string $operator New operator word.
   * @param string $mapping Built-in operator.
   */
  public function addOperator($operator, $mapping) {
    $this->customOperators[$operator] = $mapping;
  }
  
  /**
   * Convert an input string to a list of tokens.
   * @param string $input Filter string.
   * @return array[] List of tokens.
   */
  public function scan($input) {
    $this->input = str_split($input);
    $this->pop();
    $tokens = array();
    while (($token = $this->scanNext()) != null) {
      $tokens[] = $token;
    }
    return $tokens;
  }

  /**
   * Get equals operator.
   * @return string Equals oeprator.
   */
  public static function getEqualsOperator() {
    return self::$reserved[0];
  }

  /**
   * Pop a character.
   * @return string Character.
   */
  private function pop() {
    $this->current = array_shift($this->input);
    return $this->current;
  }

  /**
   * Whether current character is a space.
   * @return bool True if space.
   */
  private function isSpace() {
    return $this->current == ' ';
  }

  /**
   * Scan a string.
   * @return array Token.
   */
  private function scanString() {
    $value = '';
    $this->pop();
    while ($this->current != '"') {
      if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        // Error: Missing " (ignore it)
        return $value;
      }
      $value .= $this->current;
      $this->pop();
    }
    $this->pop();
    return array('string', $value);
  }

  /**
   * Scan a word or operator.
   * @return array Token.
   */
  private function scanWord() {
    $value = '';
    while ($this->current != null and
           strpos(self::$reserved, $this->current) === false) {
            if ($this->current == '\\') {
        $this->pop();
      }
      if ($this->current == null) {
        break;
      }
      $value .= $this->current;
      $this->pop();
    }
    $operator = strtolower($value);
    if (isset($this->customOperators[$operator]))
      $operator = $this->customOperators[$operator];
    switch ($operator) {
      case '&':
      case 'and':
        return array('&', $value);
      case '|':
      case 'or':
        return array('|', $value);
      case '!':
      case 'not':
        return array('!', $value);
      case 'contains':
        return array('contains', $value);
      case '<':
      case 'before':
        return array('<', $value);
      case '>':
      case 'after':
        return array('>', $value);
      case '<=':
        return array('<=', $value);
      case '>=':
        return array('>=', $value);
      case '=':
      case 'in':
      case 'on':
      case 'at':
        return array('=', $value);
    }
    return array('string', $value);
  }

  /**
   * Scan next token.
   * @return array Token.
   */
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
    $value = $this->current;
    switch ($value) {
      case '(':
      case ')':
      case '=':
      case '|':
      case '&':
        $this->pop();
        return array($value, $value);
      case '<':
      case '>':
      case '!':
        $this->pop();
        if ($this->current == '=') {
          $this->pop();
          return array($value . '=', $value . '=');
        }
        return array($value, $value);
    }
    return $this->scanWord();
  }
}