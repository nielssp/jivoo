<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

/**
 * Reads a third-pardy package.
 */
interface PackageReader {
  /**
   * Get list of packages in path.
   * @param string $path Path to package directory.
   * @return Package[] List of package manifests.
   */
  public function getPackages($path);
  
  /**
   * Read a package.
   * @param string $name Package name.
   * @param string $path Path to package directory.
   * @return Package|null Return package manifest or null if invalid package. 
   */
  public function read($name, $path);
}