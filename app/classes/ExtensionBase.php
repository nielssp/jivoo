<?php

abstract class ExtensionBase {

  private $extensionDir;
  
  protected $m = NULL;

  protected $e = NULL;
  
  protected $config = NULL;
  
  public final function __construct($modules, $extensions, Configuration $config) {
    $this->config = $config;
    $this->m = new Dictionary($modules, TRUE);
    $this->e = new Dictionary($extensions, TRUE);
    $this->extensionDir = get_class($this);
    $this->init();
  }

  protected function load($className) {
    if ($className[0] == 'I' AND file_exists($path = $this->getPath('interfaces/' . className($className) . '.php'))) {
      include($path);
    }
    else {
      $fileName = className($className) . '.php';
      if (file_exists($this->getPath('classes/' . $fileName))) {
        include($this->getPath('classes/' . $fileName));
      }
      else if (file_exists($this->getPath('helpers/' . $fileName))) {
        include($this->getpath('helpers/' . $fileName));
      }
      else if (file_exists($this->getpath('controllers/' . $fileName))) {
        include($this->getpath('controllers/' . $fileName));
      }
      else if (file_exists($this->getpath('modules/' . $fileName))) {
        include($this->getpath('modules/' . $fileName));
      }
    }
  }

  protected function getPath($file) {
    return p(EXTENSIONS . $this->extensionDir . '/' . $file);
  }

  protected function getLink($file) {
    return w(EXTENSIONS . $this->extensionDir . '/' . $file);
  }
  
  protected abstract function init();

  public function uninstall() {
    // nothing here
  }
}
