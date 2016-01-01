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
  /**
   * @var string Package name.
   */
  public $name = '';
  
  /**
   * @var string Package version.
   */
  public $version = '';
  
  /**
   * @var string[] List of sources.
   */
  protected $sources = array();
  
  /**
   * @var callable Prepare method.
   */
  protected $prepare = null;

  /**
   * @var callable Build method.
   */
  protected $build = null;

  /**
   * @var callable Install method.
   */
  protected $install = null;
  
  /**
   * @var string Name of manifest file.
   */
  public $manifestFile = 'extension.json';

  /**
   * @var array Content of manifest file.
   */
  public $manifest = array();
  
  /**
   * @var string
   */
  private $buildPath;

  /**
   * @var string
   */
  private $installPath;
  
  /**
   * Construct build script.
   * @param App $app Application.
   * @param string $path Script path.
   */
  public function __construct(App $app, $path) {
    parent::__construct($app);
    require $path;
  }
  
  /**
   * Log.
   * @param string $info Log message.
   */
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

  /**
   * Download a file.
   * @param string $src File source.
   * @param string $dest File destination.
   * @throws InstallException if file could not be installed.
   */
  protected function downloadFile($src, $dest) {
    if (!copy($src, $dest)) {
      throw new InstallException('Failed downloading source: ' . $src);
    }
  }
  
  /**
   * Install a file from the build path into the install path.
   * @param string $file File name.
   * @throws InstallException if file could not be downloaded.
   */
  protected function installFile($file) {
    $src = $this->buildPath . '/' . $file;
    $dest = $this->installPath . '/' . $file;
    if (!copy($src, $dest)) {
      throw new InstallException('Failed downloading source: ' . $src);
    }
  }
  
  /**
   * Run build script.
   * @param string $root Package root.
   * @throws InstallException on failure.
   */
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