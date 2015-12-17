<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\Json;
use Jivoo\Core\JsonException;

/**
 * Reads JSON manifests.
 */
abstract class JsonPackageReader implements PackageReader {
  /**
   * Get name of manifest file.
   * @return string 
   */
  public abstract function getFileName();
  
  /**
   * Get package.
   * @param array $manifest Manfiest object.
   * @param string $path Package path.
   * @return Package
   */
  public abstract function getPackage(array $manifest, $path);

  /**
   * {@inheritdoc}
   */
  public function getPackages($path, $parent = '') {
    $manifiestName = $this->getFileName();
    $dir = $path . '/' . $parent;
    $files = scandir($dir);
    $manifests = array();
    foreach ($files as $file) {
      if ($file != '.' and $file != '..') {
        if (file_exists($dir . $file . '/' . $manifiestName)) {
          $manifests[] = $this->read($parent . $file, $dir . $file);
        }
        else if (is_dir($dir . $file) and $parent == '') {
          $manifests = array_merge($manifests, $this->getPackages($dir, $file . '/'));
        }
      }
    }
    return $manifests;
  }

  /**
   * {@inheritdoc}
   */
  public function read($name, $path) {
    $file = $path . '/' . $this->getFileName();
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
    return $this->getPackage($manifest, $path);
  }
}