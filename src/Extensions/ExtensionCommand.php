<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Extensions;

use Jivoo\Core\App;
use Jivoo\Core\Cli\CommandBase;

class ExtensionCommand extends CommandBase {
  public function __construct(App $app) {
    parent::__construct($app);
    $this->addCommand('update', array($this, 'update'), tr('Update one or more extensions'));
    $this->addCommand('install', array($this, 'install'), tr('Download and install extension'));
    $this->addCommand('remove', array($this, 'remove'), tr('Remove an extension'));
    
    $this->addOption('user');
    $this->addOption('share');
  }
  
  public function getDescription($option = null) {
    return tr('Manage extensions');
  }
  
  public function update($parameters, $options) {
    if (!count($parameters)) {
      $this->put('usage: extension update [--user|--share] NAME');
      return;
    }
    $name = $parameters[0];
    // TODO: search all extension paths
    $path = $this->p('share/extensions/' . $name . '/build.php');
    if (!file_exists($path)) {
      $this->error('Build script not found: ' . $path);
      return;
    }
    $script = new BuildScript($this->app, $path);
    $this->put('Building ' . $script->name . ' ' . $script->version . '...');
    if (isset($options['user']))
      $dest = $this->p('extensions');
    else if (isset($options['share']))
      $dest = $this->p('share/extensions');
    else
      $dest = $this->p('app/extensions');
    $script->run($dest);
  }
}