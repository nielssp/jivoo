<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

/**
 * Functions for dependency version comparison.
 */
class Version {
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