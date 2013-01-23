<?php

abstract class ExtensionBase {

  private $extensions;
  
  private $extensionDir;
  
  protected $m = null;

  protected $e = null;
  
  protected $config = null;
  
  public final function __construct($m, $e, Configuration $config, Extensions $extensions) {
    $this->config = $config;
    $this->extensions = $extensions;
    $this->m = new Dictionary($m, true);
    $this->e = new Dictionary($e, true);
    $this->extensionDir = get_class($this);
    $this->init();
  }

  protected function load($className) {
    if ($className[0] == 'I' AND file_exists($path = $this->p('interfaces/' . $className . '.php'))) {
      include($path);
    }
    else {
      $fileName = $className . '.php';
      if (file_exists($this->p('classes/' . $fileName))) {
        include($this->p('classes/' . $fileName));
      }
      else if (file_exists($this->p('helpers/' . $fileName))) {
        include($this->p('helpers/' . $fileName));
      }
      else if (file_exists($this->p('controllers/' . $fileName))) {
        include($this->p('controllers/' . $fileName));
      }
      else if (file_exists($this->getpath('modules/' . $fileName))) {
        include($this->p('modules/' . $fileName));
      }
    }
  }
  
  /**
   * Get the absolute path of a file
   * @param string $key Location-identifier
   * @param string $path File
   * @return string Absolute path
   */
  public function p($key, $path = null) {
    if (isset($path)) {
      return $this->extensions->p($key, $path);
    }
    else {
      return $this->extensions->p('extensions', $this->extensionDir . '/' . $key);
    }
  }
  
  /**
   * Get the absolute path of a file relative to the public directory
   * @param string $path File
   * @return string Path
   */
  public function w($path = '') {
    return $this->extensions->w($path);
  }
  
  public function getAsset($file) {
    if (!isset($this->m->Assets)) {
      return false;
    }
    return $this->m->Assets->getAsset($this->p($file));
  }
  
  protected abstract function init();

  public function uninstall() {
    // nothing here
  }
}
