<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core;

/**
 * Subclasses of this class can be loaded by {@see ModuleLoader}.
 * @deprecated
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
  public function __construct(App $app) {
    parent::__construct($app);
    $name = Utilities::getClassName($this);
    $this->config = $this->config[$name];
  }

  public function runInit() {
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
   * Get load order for optional dependencies of module and modify a list of
   * optional dependencies.
   * @param string $class Module class (class that extends {@see LoadableModule}).
   * @return string[][] Associative array with two keys: 'before' is an array of
   * modules that must load before, and 'after' is an array of modules that must
   * load after.
   */
  public static function getLoadOrder($class) {
    $vars = get_class_vars($class);
    return array('before' => $vars['loadBefore'], 'after' => $vars['loadAfter']);
  }
}