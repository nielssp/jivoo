<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Subclasses of this class can be loaded by {@see App}.
 * @package Jivoo\Core
 */
abstract class LoadableModule extends Module {
  
  /**
   * @var string[] Names of modules that (if they are loaded) must be loaded
   * before this module.
   */
  protected static $loadAfter = array();
  
  /**
   * @var string[] Names of modules that (if they are loaded) must be loaded
   * after this module.
   */
  protected static $loadBefore = array();
  
  /**
   * Construct module.
   * @param App $app Associated application.
   */
  public final function __construct(App $app) {
    parent::__construct($app);
    $this->config = $this->config[get_class($this)];
    $this->init();
  }

  /**
   * Module initialization method.
   */
  protected function init() { }
  
  /**
   * Called after the module has been loaded.
   */
  public function afterLoad() { }

  /**
   * Get the absolute path of a file.
   * If called with a single parameter, then the name of the current module
   * is used as location identifier.
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path = null) {
    if (isset($path))
      return parent::p($key, $path);
    return parent::p(get_class($this), $key);
  }
  
  /**
   * Get load order for optional dependencies of module and modify a list of
   * optional dependencies.
   * @param string $module Module name (name class that extends {@see LoadableModule}).
   * @param string[][] $dependencies Associative array of module names and their dependencies.
   * @return string[][] Associative array of module names and their dependencies.
   */
  public static function getLoadOrder($module, $dependencies) {
    $vars = get_class_vars($module);
    foreach ($vars['loadBefore'] as $other) {
      if (!isset($dependencies[$other]))
        $dependencies[$other] = array();
      $dependencies[$other][] = $module;
    }
    if (count($vars['loadAfter']) > 0) {
      if (!isset($dependencies[$module]))
        $dependencies[$module] = array();
      foreach ($vars['loadAfter'] as $other)
        $dependencies[$module][] = $other;
    }
    return $dependencies;
  }
}