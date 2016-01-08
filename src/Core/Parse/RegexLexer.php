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

  /**
   */
  public function __construct($skipWhitespace = true, $flags = '') {
    $this->skipWhitespace = $skipWhitespace;
    $this->flags = $flags;
    if ($this->skipWhitespace)
      $this->rules['whitespace'] = '/^' . self::WHITESPACE . '/' . $flags;
  }
  
  public function __set($type, $regex) {
    $regex = str_replace('/', '\/', $regex);
    $this->rules[$type] = '/^' . $regex . '/' . $this->flags;
  }
  
  /**
   * @param string $type Token type.
   * @param callable $function Map function.
   */
  public function map($type, $function) {
    $this->map[$type] = $function;
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
      foreach ($this->rules as $type => $regex) {
        $r = preg_match($regex, $input, $matches);
        if ($r === 1) {
          $value = $matches[0];
          if (isset($this->map[$type])) {
            $value = call_user_func($this->map[$type], $value, $matches, $offset);
          }
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
        throw new ParseException('unexpected "' . $input[$offset] . '"');
      }
    }
    return $tokens;
  }
}
