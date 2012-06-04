<?php

abstract class ExtensionBase {

  private $extensionDir;
  
  protected $modules = array();
  
  protected $config = NULL;
  
  protected final function __get($module) {
    return $this->modules[$module];
  }
  
  public final function __construct($modules, Configuration $config) {
    $this->modules = $modules;
    $this->config = $config;
    $this->extensionDir = classFileName(get_class($this));
    $this->init();
  }

  protected function getLink($file) {
    return w(EXTENSIONS . $this->extensionDir . '/' . $file);
  }
  
  protected abstract function init();

  public function uninstall() {
    // nothing here
  }
}
