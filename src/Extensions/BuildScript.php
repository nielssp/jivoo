<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\App;
use Jivoo\Core\Module;
use Jivoo\Core\Paths;
use Jivoo\Core\Utilities;

class BuildScript extends Module {
  public $name = '';
  public $version = '';
  
  protected $sources = array();
  
  private $buildPath;
  
  private $installPath;
  
  public function __construct(App $app, $path) {
    parent::__construct($app);
    require $path;
  }
  
  private function fetchSources() {
    foreach ($this->sources as $source) {
      $this->download($source, $this->buildPath . '/' . basename($source));
    }
  }
  
  protected function download($src, $dest) {
    if (!copy($src, $dest)) {
      throw new \Exception('Failed downloading source: ' . $src);
    }
  }
  
  protected function install($src, $dest) {
    
  }
  
  public function run($root) {
    $this->buildPath = $this->p('tmp/build/' . $this->name);
    if (!Utilities::dirExists($this->buildPath, true, true)) {
      throw new \Exception('Could not create build directory: ' . $this->buildPath);
    }
    $this->installPath = Paths::combinePaths($root, $this->name);
    if (!Utilities::dirExists($this->installPath, true, true)) {
      throw new \Exception('Could not create install directory: ' . $this->installPath);
    }
    $this->fetchSources();
  }
}