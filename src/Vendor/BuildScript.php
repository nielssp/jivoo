<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\App;
use Jivoo\Core\Module;
use Jivoo\Core\Paths;
use Jivoo\Core\Utilities;
use Jivoo\Core\Json;

/**
 * A build script.
 */
class BuildScript extends Module {
  public $name = '';
  public $version = '';
  
  protected $sources = array();
  
  protected $prepare = null;
  
  protected $build = null;
  
  protected $install = null;
  
  public $manifestFile = 'extension.json';
  
  public $manifest = array();
  
  private $buildPath;
  
  private $installPath;
  
  public function __construct(App $app, $path) {
    parent::__construct($app);
    require $path;
  }
  
  protected function info($info) {
    $this->logger->info('[' . $this->name . '] ' . $info);
  }
  
  private function fetchSources() {
    $this->info(tr('Downloading sources:'));
    foreach ($this->sources as $source) {
      $this->info('  - ' . $source);
      $this->downloadFile($source, $this->buildPath . '/' . basename($source));
    }
  }
  
  protected function downloadFile($src, $dest) {
    if (!copy($src, $dest)) {
      throw new InstallException('Failed downloading source: ' . $src);
    }
  }
  
  protected function installFile($file) {
    $src = $this->buildPath . '/' . $file;
    $dest = $this->installPath . '/' . $file;
    if (!copy($src, $dest)) {
      throw new InstallException('Failed downloading source: ' . $src);
    }
  }
  
  public function run($root) {
    $this->buildPath = $this->p('tmp/build/' . $this->name);
    if (!Utilities::dirExists($this->buildPath, true, true)) {
      throw new InstallException('Could not create build directory: ' . $this->buildPath);
    }
    $this->installPath = Paths::combinePaths($root, $this->name);
    if (!Utilities::dirExists($this->installPath, true, true)) {
      throw new InstallException('Could not create install directory: ' . $this->installPath);
    }
    if (isset($this->prepare)) {
      $this->info(tr('Preparing...'));
      call_user_func($this->prepare, $this);
    }
    $this->fetchSources();
    if (isset($this->build)) {
      $this->info(tr('Building...'));
      call_user_func($this->build, $this);
    }
    if (isset($this->install)) {
      $this->info(tr('Installing...')); 
      call_user_func($this->install, $this);
    }
    $this->manifest['name'] = $this->name;
    $this->manifest['version'] = $this->version;
    if (isset($this->manifestFile) and isset($this->manifest)) {
      $this->info(tr('Creating manifest...'));
      $manifestFile = $this->installPath . '/' . $this->manifestFile;
      file_put_contents($manifestFile, Json::prettyPrint($this->manifest));
    }
  }
}