<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

/**
 * A repository for third-party packages.
 */
interface Repository {
  /**
   * Get names of all packages in repository.
   * @return string[] Names.
   */
  public function getPackages();

  /**
   * Load content of package (e.g. classes, includes etc.).
   * @param string $name Package name.
   * @return BuildSCript Build script.
   */
  public function getBuildScript($name);
}