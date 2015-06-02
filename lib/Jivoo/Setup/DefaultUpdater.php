<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

class DefaultUpdater extends InstallerSnippet {
  
  public function setup() {
    $this->appendStep('welcome');
    if ($this->app->hasImport('Migrations'))
      $this->appendInstaller('Jivoo\Migrations\MigrationUpdater');
    $this->appendStep('unlock');
  }
  
  public function welcome($data = null) {
    if (!$this->m->Setup->isLocked()) {
      if (!$this->m->Setup->lock())
        throw new \Exception(tr('Update required. Could not enter maintenance mode.'));
      return $this->refresh();
    }
    if (isset($this->app->config['Setup']['version'])) { 
      $this->viewData['title'] = tr(
        'Updating %1 from version %2 to version %3',
        $this->app->name, $this->app->config['Setup']['version'],
        $this->app->version
      );
    }
    else { 
      $this->viewData['title'] = tr(
        'Updating %1 to version %3',
        $this->app->name, $this->app->version
      );
    }
    if (isset($data))
      return $this->next();
    return $this->render();
  }
  
  public function unlock($data = null) {
    if ($this->m->Setup->unlock(false))
      return $this->next();
    return $this->render('jivoo/setup/default-installer/unlock.html');
  }
}
