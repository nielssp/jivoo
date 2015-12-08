<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\App;

/**
 * A local repository (a directory containing build scripts).
 */
class LocalRepository implements Repository {
  /**
   * @var App
   */
  private $app;

  /**
   * @var string
   */
  private $path;
  
  /**
   * Construct repository from a directory.
   * @param string $path Directory path.
   */
  public function __construct(App $app, $path) {
    $this->app = $app;
    $this->path = $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getPackages($parent = '') {
    $dir = $this->path . '/' . $parent;
    $files = scandir($dir);
    $names = array();
    foreach ($files as $file) {
      if ($file != '.' and $file != '..') {
        if (file_exists($dir . $file . '/build.php')) {
          $names[] = $parent . $file;
        }
        else if (is_dir($dir . $file) and $parent == '') {
          $names = array_merge($names, $this->getPackages($file . '/'));
        }
      }
    }
    return $names;
  }

  /**
   * {@inheritdoc}
   */
  public function getBuildScript($name) {
    if (!file_exists($this->path . '/' . $name . '/build.php'))
      return; // TODO: throw exception
    return new BuildScript($this->app, $this->path . '/' . $name . '/build.php');
  }
}