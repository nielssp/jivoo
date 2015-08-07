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
   */
  public function addPath($namespace, $path, $prepend = false) {
    $namespace = '\\' . trim($namespace, '\\');

    if (!isset($this->paths[$namespace]))
      $this->paths[$namespace] = array();
    
    if ($path != '')
      $path = rtrim(str_replace('\\', '/', $path), '/') . '/';
    
    if ($prepend)
      array_unshift($this->paths[$namespace], $path);
    else
      array_push($this->paths[$namespace], $path);
  }
  
  /**
   * Attempt to load a class.
   * @param string $class Fully qualified class name.
   * @return bool True on success, false on failure.
   */
  public function load($class) {
    $namespace = '\\';
    $classPath = str_replace('\\', '/', $class) . '.php';
    while (true) {
      if ($this->loadFrom($classPath, $namespace))
        return true;
      $pos = strpos($classPath, '/');
      if ($pos === false)
        break;;
      $namespace = rtrim($namespace, '\\') . '\\' . substr($classPath, 0, $pos);
      $classPath = substr($classPath, $pos + 1);
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
    }
  }
}