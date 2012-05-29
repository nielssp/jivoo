<?php

abstract class ExtensionBase {
  
  protected $modules = array();
  
  protected $config = NULL;
  
  protected final function __get($module) {
    return $this->modules[$module];
  }
  
  public final function __construct($modules, Configuration $config) {
    $this->modules = $modules;
    $this->config = $config;
    $this->init();
  }
  
  protected abstract function init();
}