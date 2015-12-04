<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\Parse\ParseInput;

/**
 * A composer dependency.
 */
class ComposerDependency implements Dependency {
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var string
   */
  private $constraint;
  
  public function __construct($name, $constraint) {
    $this->name = $name;
    $this->constraint = $constraint;
  }
  
  /**
   * {@inheritDoc}
   */
  public function getName() {
    return $this->name;
  }
  
  /**
   * @param ParseInput $input 
   */
  private function parseWhitespace(ParseInput $input) {
    while ($input->accept(' ') or $input->accept("\t")) {
    }
  }
  
  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  private function parseDisjunction(ParseInput $input, $version) {
    while (true) {
      if ($this->parseConjunction($input, $version))
        return true;
      $this->parseWhitespace($input);
      if (!$input->accept('|'))
        return false;
      $input->expect('|');
      $this->parseWhitespace($input);
    }
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  private function parseConjunction(ParseInput $input, $version) {
    while (true) {
      if (!$this->parseRange($input, $version))
        return false;
      $this->parseWhitespace($input);
      if ($this->peek() === null)
        return true;
      if ($input->accept(','))
        $this->parseWhitespace($input);
    }
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  private function parseRange(ParseInput $input, $version) {
    $a = $this->parseExact($input);
    $this->parseWhitespace($input);
    if (!$this->accept('-'))
      return $a;
    $this->parseWhitespace($input);
    $b = $this->parseExact($input);
    return version_compare($version, $a, '>=')
      and version_compare($version, $b, '<');
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  private function parseVersion(ParseInput $input, $version) {
    $op = $this->parseOperator($input);
    if (isset($op)) {
      $a = $this->parseExact($input);
      if ($op == '~')
        return false; //TODO
      if ($op == '^')
        return false; //TODO
      return version_compare($version, $a, $op);
    }
    return $this->parseWildcard($input, $version);
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  private function parseWildcard(ParseInput $input, $version) {
    $this->parseWhitespace($input);
    $int = '';
    while (true) {
      $part = $this->parseVersionPart($input);
      if (!isset($part)) {
        if ($input->accept('*')) {
          
        }
        else {
          break;
        }
      }
      else {
        
      }
    }
  }

  /**
   * @param ParseInput $input
   * @return string
   */
  private function parseExact(ParseInput $input) {
    $this->parseWhitespace($input);
    $version = '';
    while (true) {
      $part = $this->parseVersionPart($input);
      if (!isset($part))
        break;
      if ($version != '')
        $version .= '.';
      $version .= $int;
      $input->accept('.') or $this->accept('-');
    }
    return $version;
  }
  
  private function parseVersionPart(ParseInput $input) {
    $part = $this->parseInt($input);
    if (!isset($part)) {
      $part = $this->parseNonInt($input);
      if (!isset($part))
        return null;
    }
    return $part;
  }

  /**
   * @param ParseInput $input
   * @return string
   */
  private function parseNonInt(ParseInput $input) {
    $str = '';
    while (true) {
      $c = $input->peek();
      if ($c == ' ' or $c == "\t" or $c == '-' or $c == '.' or is_numeric($c)) {
        break;
      }
      $str .= $input->pop();
    }
    if ($str == '')
      return null;
    return $str;
  }

  /**
   * @param ParseInput $input
   * @return string
   */
  private function parseInt(ParseInput $input) {
    while (is_numeric($input->peek())) {
      $int .= $input->pop();
    }
    if ($int == '')
      return null;
    return $int;
  }

  /**
   * @param ParseInput $input
   * @return string
   */
  private function parseOperator(ParseInput $input) {
    $this->parseWhitespace($input);
    if ($input->accept('<')) {
      if ($input->accept('='))
        return '<=';
      return '<';
    }
    if ($input->accept('>')) {
      if ($input->accept('='))
        return '>=';
      return '>';
    }
    if ($input->accept('!')) {
      $input->expect('=');
      return '!=';
    }
    if ($input->accept('~'))
      return '~';
    if ($input->accept('^'))
      return '^';
    return null;
  }
  
  /**
   * {@inheritDoc}
   */
  public function checkVersion($version) {
    // Grammar:
    //
    // disjunction   ::= conjunction "||" disjunction
    //                 | conjunction
    // conjunction   ::= range [","] conjunction
    //                 | range
    // range         ::= exact ["-" exact]
    //                 | version
    // version       ::= (>|>=|<|<=|!=|~|^) exact
    //                 | wildcard
    // exact         ::= int {"." int} ["-" stability]
    // wildcard      ::= intx {"." intx} ["-" stability]
    
    // int           ::= digit {digit}
    // intx          ::= int | "*"
    // digit         ::= "0" | ... | "9"
  
    
    return true;
  }
}