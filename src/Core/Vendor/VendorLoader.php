<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

use Jivoo\Core\VendorException;

/**
 * Imports third-party libraries (e.g. packages installed using Composer) from
 * one or more directories. 
 */
class VendorLoader {
  /**
   * @var IPackageReader[]
   */
  private $paths = array();
  
  /**
   * Add a vendor-directory path.
   * @param string $path Path.
   */
  public function addPath($path, IPackageReader $reader = null) {
    if (!isset($reader))
      $reader = true;
    $path = Utilities::convertRealPath($path);
    $this->paths[$path] = $reader; 
  }
  
  /**
   * Remove a vendor-directory path.
   * @param string $path Path.
   */
  public function removePath($path) {
    $path = Utilities::convertRealPath($path);
    if (isset($this->paths[$path]))
      unset($this->paths[$path]);
  }
  
  /**
   * Find and import a library.
   * @param string $name Library name.
   */
  public function import($name) {
    $manifest = null;
    foreach ($this->paths as $path => $reader) {
      $path = $path . '/' . $name;
      if (is_dir($path)) {
        $manifest = $reader->read($name, $path);
        if (isset($manifest))
          break;
      }
    }
    if (!isset($manifest))
      throw new VendorException(tr('Could not import library: %1', $name));
    $manifest->registerAutoloader();
  }
  
  public function getLibraries(IPackageReader $reader = null) {
    
  }
}