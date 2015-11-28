<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Vendor;

use Jivoo\Core\App;
use Jivoo\Core\Cli\CommandBase;

class VendorCommand extends CommandBase {
  public function __construct(App $app) {
    parent::__construct($app);
    $this->addCommand('update', array($this, 'update'), tr('Update one or more libraries'));
    $this->addCommand('install', array($this, 'install'), tr('Download and install libraries'));
    $this->addCommand('remove', array($this, 'remove'), tr('Remove a library'));
    $this->addCommand('list', array($this, 'list_'), tr('List installed/available libraries'));
    
    $this->addOption('user');
    $this->addOption('share');
  }
  
  public function getDescription($option = null) {
    return tr('Manage third-party libraries');
  }
  
  public function list_($paramters, $options) {
    
  }
  
  private function removeDir($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
      if ($file != '.' and $file != '..') {
        $path = $dir . '/' . $file;
        if (is_dir($path))
          $this->removeDir($path);
        else
          unlink($path);
      }
    }
    rmdir($dir);
  }
  
  public function remove($parameters, $options) {
    if (!count($parameters)) {
      $this->put('usage: vendor remove [--user|--share] NAME');
      return;
    }
    $name = $parameters[0];
    if (isset($options['user']))
      $dir = $this->p('vendor/' . $name);
    else if (isset($options['share']))
      $dir = $this->p('share/vendor/' . $name);
    else
      $dir = $this->p('app/vendor/' . $name);
    if (!file_exists($dir)) {
      $this->error('Directory not found: ' . $dir);
      return;
    }
    $this->put(tr('The following directory will be deleted:'));
    $this->put('  - ' . $dir);
    $this->put();
    $confirm = $this->confirm(tr('Remove %1?', $name), true);
    if ($confirm) {
      $this->put(tr('Removing %1...', $name));
      $this->removeDir($dir);
    }
  }
  
  public function update($parameters, $options) {
    if (!count($parameters)) {
      $this->put('usage: vendor update [--user|--share] NAME [NAMES...]');
      return;
    }
    foreach ($parameters as $name) {
      // TODO: search all extension paths
      $path = $this->p('share/vendor/' . $name . '/build.php');
      if (!file_exists($path)) {
        $this->error('Build script not found: ' . $path);
        return;
      }
      $script = new BuildScript($this->app, $path);
      $this->put('Building ' . $script->name . ' ' . $script->version . '...');
      if (isset($options['user']))
        $dest = $this->p('vendor');
      else if (isset($options['share']))
        $dest = $this->p('share/vendor');
      else
        $dest = $this->p('app/vendor');
      $script->run($dest);
    }
  }
}