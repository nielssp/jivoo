<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Autoloader;

/**
 * A composer package.
 */
class ComposerPackage implements Package {
  /**
   * @var array Manifest.
   */
  protected $manifest;
  
  /**
   * @var string Package root.
   */
  protected $path;
  
  /**
   * Construct composer package.
   * @param array $manifest Package manifest.
   * @param string $path Package root.
   */
  public function __construct($manifest, $path) {
    $this->manifest = $manifest;
    $this->path = $path;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->manifest['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getManifest() {
    return $this->manifest;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    if (!isset($this->manifest['require']))
      return array();
    $deps = array();
    foreach ($this->manifest['require'] as $name => $constraint)
      $deps[] = new ComposerDependency($name, $constraint);
    return $deps;
  }

  /**
   * {@inheritdoc}
   */
  public function getConflicts() {
    if (!isset($this->manifest['conflict']))
      return array();
    $deps = array();
    foreach ($this->manifest['conflict'] as $name => $constraint)
      $deps[] = new ComposerDependency($name, $constraint);
    return $deps;
  }

  /**
   * {@inheritdoc}
   */
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
      assume(is_array($this->manifest['autoload']['psr-0']));
      foreach ($this->manifest['autoload']['psr-0'] as $namespace => $path) {
        if (is_array($path)) {
          foreach ($path as $p)
            $autoloader->addPath($namespace, $this->path . '/' . trim($p, '/'), false, true);
        }
        else {
          $autoloader->addPath($namespace, $this->path . '/' . trim($path, '/'), false, true);
        }
      }
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