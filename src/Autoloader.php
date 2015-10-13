<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo;

/**
 * A PSR-4 autoloader implementation. A singleton instance of this class is
 * used by Jivoo.
 */
class Autoloader {
  /**
   * @var Autoloader
   */
  private static $instance = null;
  
  /**
   * @var string[][]
   */
  private $paths = array();
  
  /**
   * @var bool
   */
  private $psr0 = true;

  /**
   * @var string[][]
   */
  private $psr0Paths = array();
  
  /**
   * Get singleton autoloader instance.
   * @return Autoloader Autoloader.
   */
  public static function getInstance() {
    if (!isset(self::$instance))
      self::$instance = new self();
    return self::$instance;
  }
  
  /**
   * Register autoloader object.
   * @param bool $prepend If true, the autolaoder will be prepended instead of
   * appended.
   */
  public function register($prepend = false) {
    spl_autoload_register(array($this, 'load'), true, $prepend);
  }
  
  /**
   * Unregister autoloader object.
   */
  public function unregister() {
    spl_autoload_unregister(array($this, 'load'));
  }
  
  /**
   * Add an autload directory for a namespace.
   * @param string $namespace Namespace prefix (i.e. with trailing backslash).
   * @param string $path Directory path.
   * @param bool $prepend If true, the path will be prepended instead of
   * appended.
   * @param bool $psr0 Whether to use PSR-0 instead of PSR-4 for this path.
   */
  public function addPath($namespace, $path, $prepend = false, $psr0 = false) {
    if ($path != '')
      $path = rtrim(str_replace('\\', '/', $path), '/') . '/';
    
    if ($psr0) {
      $this->psr0 = true;
      if (!isset($this->psr0Paths[$namespace]))
        $this->psr0Paths[$namespace] = array();
      if ($prepend)
        array_unshift($this->psr0Paths[$namespace], $path);
      else
        array_push($this->psr0Paths[$namespace], $path);
    }
    else {
      $namespace = '\\' . trim($namespace, '\\');
  
      if (!isset($this->paths[$namespace]))
        $this->paths[$namespace] = array();
      if ($prepend)
        array_unshift($this->paths[$namespace], $path);
      else
        array_push($this->paths[$namespace], $path);
    }
  }
  
  /**
   * Attempt to load a class.
   * @param string $class Fully qualified class name.
   * @return bool True on success, false on failure.
   */
  public function load($class) {
    if ($this->psr0 and $this->loadPsr0($class))
      return true;
    $namespace = '\\';
    $classPath = str_replace('\\', '/', $class) . '.php';
    while (true) {
      if ($this->loadFrom($classPath, $namespace))
        return true;
      $pos = strpos($classPath, '/');
      if ($pos === false) {
        if (!$this->psr0)
          break;
        $pos = strpos($classPath, '_');
        if ($pos === false)
          break;
      }
      $namespace = rtrim($namespace, '\\') . '\\' . substr($classPath, 0, $pos);
      $classPath = substr($classPath, $pos + 1);
    }
    return false;
  }
  
  public function loadPsr0($class) {
    $classPath = str_replace('\\', '/', $class) . '.php';
    foreach ($this->psr0Paths as $prefix => $paths) {
      if (strncmp($prefix, $class, strlen($prefix)) === 0) {
        foreach ($paths as $path) {
          $file = $path . str_replace('_', '/', $classPath);
          if (file_exists($file)) {
            require $file;
            return true;
          }
        }
      }
    }
    return false;
  }

  /**
   * @param string $classPath
   * @param string $namespace
   * @return bool
   */
  private function loadFrom($classPath, $namespace) {
    if (!isset($this->paths[$namespace]))
      return false;
    foreach ($this->paths[$namespace] as $path) {
      $file = $path . $classPath;
      if (file_exists($file)) {
        require $file;
        return true;
      }
      else if ($this->psr0) {
        $file = $path . str_replace('_', '/', $classPath);
        if (file_exists($file)) {
          require $file;
          return true;
        }
      }
    }
  }
}
