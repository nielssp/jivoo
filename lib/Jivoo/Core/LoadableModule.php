<?php
/**
 * Subclasses of this class can be loaded by {@see App}.
 */
abstract class LoadableModule extends Module {
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
}