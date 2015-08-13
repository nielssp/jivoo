<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Vendor;


class DefaultPackageReader implements PackageReader {
  /**
   * @var DefaultPackageReader
   */
  private static $instance = null; 
  
  private function __construct() {}
  
  /**
   * Get singleton instance of default package reader.
   * @return DefaultPackageReader Instace.
   */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new self();
    return self::$instance;
  }
  
  /**
   * {@inheritdoc}
   */
  public function read($name, $path) {
    if (!file_exists($path . '/composer.json'))
      return null;
  }
}