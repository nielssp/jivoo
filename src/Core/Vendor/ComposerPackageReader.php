<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;

use Jivoo\Core\Json;
use Jivoo\Core\JsonException;

/**
 * Reads composer packages.
 */
class ComposerPackageReader implements PackageReader {
  /**
   * {@inheritdoc}
   */
  public function read($name, $path) {
    $file = $path . '/composer.json';
    if (!file_exists($file))
      return null;
    try {
      $manifest = Json::decodeFile($file);
    }
    catch (JsonException $e) {
      return null;
    }
    if (!isset($manifest['name']))
      $manifest['name'] = $name;
    return new ComposerPackage($manifest, $path);
  }
}