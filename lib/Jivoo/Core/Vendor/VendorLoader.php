<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

/**
 * Imports third-party libraries (e.g. packages installed using Composer) from
 * one or more directories. 
 */
class VendorLoader {
  
  private $paths = array();
  
  public function addPath($path) {
    $path = Utilities::convertRealPath($path);
    $this->paths[$path] = true; 
  }
  
  public function removePath($path) {
    $path = Utilities::convertRealPath($path);
    if (isset($this->paths[$path]))
      unset($this->paths[$path]);
  }
  
  public function import($name) {
    
  }
}