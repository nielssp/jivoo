<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Autoloader;

/**
 * A pacakge dependency.
 */
interface Dependency {
  /**
   * Get name of dependency.
   * @return string
   */
  public function getName();

  /**
   * Check version of dependency.
   * @param string $version Version string.
   * @return bool True if version satisfies the dependency, false otherwise .
   */
  public function checkVersion($version);
}