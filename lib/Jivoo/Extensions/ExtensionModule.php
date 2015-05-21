<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\Module;
use Jivoo\Core\App;
use Jivoo\Core\Config;

/**
 * A module that can be loaded by an extension.
 */
abstract class ExtensionModule extends Module {
  /**
   * @var string Extension directory name.
   */
  private $dir;

  /**
   * @var ExtensionInfo Extension information.
   */
  protected $info;
  
  /**
   * @var Map Map of loaded extension modules.
   */
  protected $e = null;
  
  /**
   * @var string[] Names of extension modules required by this module.
   */
  protected $extensions = array();

  /**
   * Construct extension module.
   * @param App $app Associated application.
   * @param ExtensionInfo $info Extension information.
   * @param Config $config Extension configuration.
   */
  public final function __construct(App $app, ExtensionInfo $info, Config $config) {
    parent::__construct($app);
    $this->e = $this->m->Extensions->getModules($this->extensions);
    $this->config = $config;
    $this->dir = $info->canonicalName;
    $this->info = $info;
    $this->init();
  }

  /**
   * Module initialization.
   */
  protected function init() { }

  /**
   * Called after loading extension.
   */
  public function afterLoad() { }

  /**
   * Get the absolute path of a file.
   * 
   * If called with a single parameter, then the current extension directory is
   * used as base path.
   * 
   * @param string $key Location identifier.
   * @param string $path File.
   * @return string Absolute path.
   */
  public function p($key, $path = null) {
    if (isset($path))
      return parent::p($key, $path);
    return $this->info->p($this->app, $key);
  }
  
  /**
   * Get an asset.
   * 
   * If called with a single parameter, then the current extension directory is
   * used as base path.
   * 
   * @param string $key Location identifier.
   * @param string $path File.
   * @return string Asset path.
   */
  public function getAsset($key, $path = null) {
    if (isset($path))
      return $this->m->Assets->getAsset($key, $path);
    return $this->info->getAsset($this->m->Assets, $key);
  }
}
