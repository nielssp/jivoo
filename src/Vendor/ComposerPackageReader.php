<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\Json;
use Jivoo\Core\JsonException;

/**
 * Reads composer packages.
 */
class ComposerPackageReader extends JsonPackageReader{
  /**
   * {@inheritdoc}
   */
  public function getFileName() {
    return 'composer.json';
  }
  
  /**
   * {@inheritdoc}
   */
  public function getPackage(array $manifest, $path) {
    return new ComposerPackage($manifest, $path);
  }
}
