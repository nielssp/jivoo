<?php

abstract class LoadableModule extends Module {

  public final function __construct(App $app) {
    parent::__construct($app);
    $this->init();
  }

  protected function init() {

  }

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
      return $this->p($key, $path);
    return $this->p(get_class($this), $key);
  }
}