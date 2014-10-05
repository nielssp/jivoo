<?php

abstract class ExtensionModule extends Module {

  private $dir;

  protected $e = null;

  public final function __construct(App $app, AppConfig $config, $dir) {
    parent::__construct($app);
    $this->e = $this->m->Extensions;
    $this->config = $config;
    $this->dir = $dir;
    $this->init();
  }

  protected function init() { }

  public function uninstall() { }
  
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
    return parent::p('extensions', $this->dir . '/' . $key);
  }
}
