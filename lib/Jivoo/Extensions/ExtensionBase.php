<?php
/**
 * Extension base class
 * @package PeanutCMS\Extensions
 */
abstract class ExtensionBase extends Module {

  private $extensionDir;

  protected $e = null;

  public final function __construct(App $app, AppConfig $config, $extensions = array()) {
    $this->inheritElements('modules');
    parent::__construct($app);
    $this->config = $config;
    $this->e = new Map($extensions, true);
    $this->extensionDir = get_class($this);
    $this->init();
  }

  protected function load($className) {
    $fileName = $className . '.php';
    if (file_exists($this->p($fileName))) {
      include($this->p($fileName));
    }
    else if (file_exists($this->p('lib/' . $fileName))) {
      include($this->p('lib/' . $fileName));
    }
    else if (file_exists($this->p('helpers/' . $fileName))) {
      include($this->p('helpers/' . $fileName));
    }
    else if (file_exists($this->p('controllers/' . $fileName))) {
      include($this->p('controllers/' . $fileName));
    }
    else if (file_exists($this->p('modules/' . $fileName))) {
      include($this->p('modules/' . $fileName));
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
      return parent::p($key, $path);
    }
    else {
      return parent::p('extensions', $this->extensionDir . '/' . $key);
    }
  }


  public function getAsset($file) {
    if (!isset($this->m->Assets)) {
      return false;
    }
    return $this->m->Assets
      ->getAsset('extensions', $this->extensionDir . '/' . $file);
  }

  protected function init() { }

  public function uninstall() { }
}
