<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

class DefaultInstaller extends InstallerSnippet {
  
  public function setup() {
    $this->appendStep('welcome', true);
    $this->appendInstaller('Jivoo\Setup\LockInstaller');
    if ($this->app->hasImport('Databases'))
      $this->appendInstaller('Jivoo\Databases\DatabaseInstaller');
    if ($this->app->hasImport('Migrations'))
      $this->appendInstaller('Jivoo\Migrations\MigrationInstaller');
    $this->appendStep('unlock');
  }
  
  public function welcome($data = null) {
    $this->viewData['title'] = tr('Welcome to %1', $this->app->name);
    if (isset($data))
      return $this->next();
    return $this->render();
  }
  
  public function unlock($data = null) {
    if ($this->m->Setup->unlock(false))
      return $this->next();
    return $this->render();
  }
}
