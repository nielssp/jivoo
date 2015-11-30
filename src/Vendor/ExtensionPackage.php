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
}