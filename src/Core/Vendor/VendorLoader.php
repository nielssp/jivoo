<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

use Jivoo\Core\VendorException;
use Jivoo\Core\Paths;
use Jivoo\Core\App;
use Jivoo\Autoloader;

/**
 * Imports third-party libraries (e.g. packages installed using Composer) from
 * one or more directories. 
 */
class VendorLoader {
  /**
   * @var App
   */
  private $app;

  /**
   * @var PackageReader[]
   */
  private $paths = array();
  
  /**
   * @var bool[]
   */
  private $imported = array();
  
  public function __construct(App $app) {
    $this->app = $app;
  }
  
  /**
   * Add a vendor-directory path.
   * @param string $path Path.
   */
  public function addPath($path, PackageReader $reader = null) {
    if (!isset($reader))
      $reader = true;
    $path = Paths::convertRealPath($path);
    $this->paths[$path] = $reader; 
  }
  
  /**
   * Remove a vendor-directory path.
   * @param string $path Path.
   */
  public function removePath($path) {
    $path = Paths::convertRealPath($path);
    if (isset($this->paths[$path]))
      unset($this->paths[$path]);
  }
  
  /**
   * Find and import a library.
   * @param string $name Library name.
   */
  public function import($name) {
    if (isset($this->imported[$name]))
      return;
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
    $manifest->load(Autoloader::getInstance());
    $this->imported[$name] = true;
  }
  
  public function getLibraries(PackageReader $reader = null) {
    
  }
}