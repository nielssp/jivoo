<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\Parse\ParseInput;

/**
 * Functions for dependency version comparison.
 */
class Version {

  /**
   * @param ParseInput $input 
   */
  public static function parseWhitespace(ParseInput $input) {
    while ($input->accept(' ') or $input->accept("\t")) {
    }
  }
  
  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  public static function parseDisjunction(ParseInput $input, $version) {
    while (true) {
      if (self::parseConjunction($input, $version))
        return true;
      self::parseWhitespace($input);
      if (!$input->accept('|'))
        return false;
      $input->expect('|');
      self::parseWhitespace($input);
    }
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  public static function parseConjunction(ParseInput $input, $version) {
    while (true) {
      if (!self::parseRange($input, $version))
        return false;
      self::parseWhitespace($input);
      if (self::peek() === null)
        return true;
      if ($input->accept(','))
        self::parseWhitespace($input);
    }
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  public static function parseRange(ParseInput $input, $version) {
    $a = self::parseExact($input);
    self::parseWhitespace($input);
    if (!self::accept('-'))
      return $a;
    self::parseWhitespace($input);
    $b = self::parseExact($input);
    return version_compare($version, $a, '>=')
      and version_compare($version, $b, '<');
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  public static function parseVersion(ParseInput $input, $version) {
    $op = self::parseOperator($input);
    if (isset($op)) {
      $a = self::parseExact($input);
      if ($op == '~')
        return false; //TODO
      if ($op == '^')
        return false; //TODO
      return version_compare($version, $a, $op);
    }
    return self::parseWildcard($input, $version);
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  public static function parseWildcard(ParseInput $input, $version) {
    self::parseWhitespace($input);
    $wildcard = array('0');
    $version = '0.' . $version;
    $next = '';
    while (true) {
      $part = self::parseVersionPart($input);
      if (!isset($part)) {
        if ($input->accept('*')) {
          $next = $wildcard;
          $next[count($next) - 1] += 1;
          return version_compare($version, implode('.', $wildcard), '>=')
            and version_compare($version, implode('.', $next), '<');
        }
        else {
          break;
        }
      }
      else {
        $wildcard[] = $part;
      }
      $input->accept('.') or $input->accept('-');
    }
    return version_compare($version, implode('.', $wildcard), '==');
  }

  /**
   * @param ParseInput $input
   * @return string
   */
  public static function parseExact(ParseInput $input) {
    self::parseWhitespace($input);
    $version = '';
    while (true) {
      $part = self::parseVersionPart($input);
      if (!isset($part))
        break;
      if ($version != '')
        $version .= '.';
      $version .= $part;
      $input->accept('.') or $input->accept('-');
    }
    return $version;
  }
  
  public static function parseVersionPart(ParseInput $input) {
    $part = self::parseInt($input);
    if (!isset($part)) {
      $part = self::parseNonInt($input);
      if (!isset($part))
        return null;
    }
    return $part;
  }

  /**
   * @param ParseInput $input
   * @return string
   */
  public static function parseNonInt(ParseInput $input) {
    $str = '';
    while (true) {
      $c = $input->peek();
      if (is_numeric($c) or in_array($c, array(null, ' ', "\t", '-', '.', '*', '!', '=', '>', '<'))) {
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
  public static function parseInt(ParseInput $input) {
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
  public static function parseOperator(ParseInput $input) {
    self::parseWhitespace($input);
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
   * Perform a version comparison.
   * @param string $actualVersion Actual version, see {@see version_compare()}
   * for valid version strings.
   * @param string $versionComparison Version comparison: an operator followed
   * by a valid version string. Supported opertors are: <>, <=, >=, ==, !=, <,
   * >, and =.
   * @return boolean
   */
  public static function compare($actualVersion, $versionComparison) {
    while (!empty($versionComparison)) {
      if (preg_match('/^ *(<>|<=|>=|==|!=|<|>|=) *([^ <>=!]+) *(.*)$/', $versionComparison, $matches) !== 1)
        return false;
      $operator = $matches[1];
      $expectedVersion = $matches[2];
      if (!version_compare($actualVersion, $expectedVersion, $operator))
        return false;
      $versionComparison = $matches[3];
    }
    return true;
  }
}
