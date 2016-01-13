<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Parse;

/**
 * A simple lexer using regular expressions.
 */
class RegexLexer {
  const WHITESPACE = "[ \t\n]+";
  
  private $skipWhitespace;
  
  private $flags;
  
  /**
   * @var array
   */
  private $rules = array();
  
  private $map = array();
  
  private $typeMap = array();

  /**
   */
  public function __construct($skipWhitespace = true, $flags = '') {
    $this->skipWhitespace = $skipWhitespace;
    $this->flags = $flags;
    if ($this->skipWhitespace) {
      $this->rules['whitespace'] = '/^' . self::WHITESPACE . '/' . $flags;
      $this->typeMap['whitespace'] = 'whitespace';
    }
  }
  
  public function __set($rule, $regex) {
    $regex = str_replace('/', '\/', $regex);
    $this->rules[$rule] = '/^(?:' . $regex . ')/' . $this->flags;
    $this->typeMap[$rule] = $rule;
  }
  
  /**
   * @param string $type Token type.
   * @param callable $function Map function.
   */
  public function map($rule, $function) {
    $this->map[$rule] = $function;
  }
  
  public function mapType($rule, $type) {
    $this->typeMap[$rule] = $type;
  }

  public function __invoke($input) {
    $offset = 0;
    $length = strlen($input);
    $tokens = array();
    if ($this->skipWhitespace) {
      $r = preg_match($this->rules['whitespace'], $input, $matches);
      if ($r === 1) {
        $skip = strlen($matches[0]);
        $input = substr($input, $skip);
        $offset += $skip;
      }
    }
    while ($offset < $length) {
      $found = false;
      foreach ($this->rules as $rule => $regex) {
        $r = preg_match($regex, $input, $matches);
        if ($r === 1) {
          $value = $matches[0];
          if (isset($this->map[$rule])) {
            $value = call_user_func($this->map[$rule], $value, $matches, $offset);
          }
          $type = $this->typeMap[$rule];
          $tokens[] = array($type, $value, $matches, $offset);
          $skip = strlen($matches[0]);
          $input = substr($input, $skip);
          $offset += $skip;
          if ($this->skipWhitespace) {
            $r = preg_match($this->rules['whitespace'], $input, $matches);
            if ($r === 1) {
              $skip = strlen($matches[0]);
              $input = substr($input, $skip);
              $offset += $skip;
            }
          }
          $found = true;
          break;
        }
      }
      if (!$found) {
        throw new ParseException('unexpected "' . $input[$offset] . '" at ' . $offset);
      }
    }
    return $tokens;
  }
}
