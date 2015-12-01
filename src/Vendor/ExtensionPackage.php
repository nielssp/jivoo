<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Autoloader;

/**
 * An extension package.
 */
class ExtensionPackage extends ComposerPackage {
  /**
   * {@inheritdoc}
   */
  public function load(Autoloader $autoloader) {
    parent::load($autoloader);
  }
  
  
  /**
   * Replace PHP-style variables in a string if they correspond to keys in the
   * manifest.
   * @param string $string String containing variables.
   * @return string String with known variables replaced.
   */
  public function replaceVariables($string) {
    $manifest = $this->manifest;
    return preg_replace_callback(
      '/\$([a-z0-9]+)/i', 
      function ($matches) use ($manifest) {
        if (isset($manifest[$matches[1]]))
          return $manifest[$matches[1]];
        return $matches[0];
      },
      $string
    );
  }
}