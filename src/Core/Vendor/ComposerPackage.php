<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

use Jivoo\Autoloader;

class ComposerPackage implements Package {
  private $manifest;
  
  private $path;
  
  public function __construct($manifest, $path) {
    $this->manifest = $manifest;
    $this->path = $path;
  }
  
  public function getName() {
    return $this->manifest['name'];
  }
  
  public function load(Autoloader $autoloader) {
    if (!isset($this->manifest['autoload']) or !is_array($this->manifest['autoload']))
      return;
    if (isset($this->manifest['autoload']['psr-4'])) {
      assume(is_array($this->manifest['autoload']['psr-4']));
      foreach ($this->manifest['autoload']['psr-4'] as $namespace => $path) {
        if (is_array($path)) {
          foreach ($path as $p)
            $autoloader->addPath($namespace, $this->path . '/' . trim($p, '/'));
        }
        else {
            $autoloader->addPath($namespace, $this->path . '/' . trim($path, '/'));
        }
      }
    }
    if (isset($this->manifest['autoload']['psr-0'])) {
      assume(false, 'PSR-0 support not implemented');
    }
    if (isset($this->manifest['autoload']['classmap'])) {
      assume(false, 'classmap support not implemented');
    }
    if (isset($this->manifest['autoload']['files'])) {
      assume(is_array($this->manifest['autoload']['files']));
      foreach ($this->manifest['autoload']['files'] as $file) {
        require $this->path . '/' . $file;
      }
    }
  }
}