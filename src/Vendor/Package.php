<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Autoloader;

/**
 * A third-party package.
 */
interface Package {
  /**
   * Name of packages.
   * @return string Name.
   */
  public function getName();

  /**
   * Get content of package manifest.
   * @return array Manifest object.
   */
  public function getManifest();
  
  /**
   * Load content of package (e.g. classes, includes etc.).
   * @param Autoloader $autoloader Class autoloader.
   */
  public function load(Autoloader $autoloader);
}