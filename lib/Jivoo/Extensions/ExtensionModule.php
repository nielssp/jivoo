<?php

abstract class ExtensionModule extends Module {

  private $dir;

  protected $info;
  
  protected $e = null;
  
  protected $extensions = array();

  public final function __construct(App $app, ExtensionInfo $info, AppConfig $config) {
    parent::__construct($app);
    $this->e = $this->m->Extensions->getModules($this->extensions);
    $this->config = $config;
    $this->dir = $info->dir;
    $this->info = $info;
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
