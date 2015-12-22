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
        break;
      $input->expect('|');
      self::parseWhitespace($input);
    }
    return false;
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
      if ($input->accept(','))
        self::parseWhitespace($input);
      $c = $input->peek();
      if (in_array($c, array(null, '|')))
        break;
    }
    return true;
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool
   */
  public static function parseRange(ParseInput $input, $version) {
    $op = self::parseOperator($input);
    if (isset($op)) {
      $a = self::parseExact($input);
      if ($op == '~') {
        if (version_compare($version, implode('.', $a), '<'))
          return false;
        if (count($a) < 2)
          return true;
        $a[count($a) - 2] += 1; // TODO: increment part
        $a[count($a) - 1] = 0;
        return version_compare($version, implode('.', $a), '<');
      }
      if ($op == '^') {
        if (version_compare($version, implode('.', $a), '<'))
          return false;
        if (count($a) < 2)
          return true;
        if ($a[0] < 1) {
          $a = array($a[0], $a[1] + 1);
          return version_compare($version, implode('.', $a), '<');
        }
        else {
          $a = array($a[0] + 1, 0);
          return version_compare($version, implode('.', $a), '<');
        }
      }
      return version_compare($version, implode('.', $a), $op);
    }
    $a = self::parseWildcard($input);
    if (is_bool($a)) // is wildcard
      return $a;
    self::parseWhitespace($input);
    if (!$input->accept('-')) {
      return version_compare($version, implode('.', $a), '==');
    }
    self::parseWhitespace($input);
    $b = self::parseExact($input);
    return version_compare($version, implode('.', $a), '>=')
      and version_compare($version, implode('.', $b), '<');
  }

  /**
   * @param ParseInput $input
   * @param string $version
   * @return bool|string[]
   */
  public static function parseWildcard(ParseInput $input, $version) {
    self::parseWhitespace($input);
    $wildcard = array();
    while (true) {
      $part = self::parseVersionPart($input);
      if (!isset($part)) {
        if ($input->accept('*')) {
          $wildcard = array_merge(array(0), $wildcard);
          $version = '0.' . $version;
          $next = $wildcard;
          $next[count($next) - 1] += 1; // TODO: increment part
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
    return $wildcard;
  }

  /**
   * @param ParseInput $input
   * @return string[]
   */
  public static function parseExact(ParseInput $input) {
    self::parseWhitespace($input);
    $version = array();
    while (true) {
      $part = self::parseVersionPart($input);
      if (!isset($part))
        break;
      $version[]= $part;
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
