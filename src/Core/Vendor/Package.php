<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

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
   * Load content of package (e.g. classes, includes etc.).
   * @param Autoloader $autoloader Class autoloader.
   */
  public function load(Autoloader $autoloader);
}