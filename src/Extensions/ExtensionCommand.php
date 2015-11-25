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
    $path = $this->p('share/extensions/' . $name . '/build.php');
    $script = new BuildScript($this->app, $path);
    $this->put('Building ' . $script->name . ' ' . $script->version . '...');
    $script->run($this->p('extensions'));
  }
}