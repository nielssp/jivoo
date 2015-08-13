<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

/**
 * 
 */
interface IPackageReader {
  /**
   * Read a package.
   * @param string $name Package name.
   * @param string $path Path to package directory.
   * @return IPackage|null Return package manifest or null if invalid package. 
   */
  public function read($name, $path);
}