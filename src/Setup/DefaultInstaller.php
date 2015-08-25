<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

/**
 * An installer that presents a welcome-page, installs the lock and maintenance user,
 * runs the database installer (if enabled), runs the migration installer (if
 * enabled), and exits maintenance mode.
 */
class DefaultInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */  
  public function setup() {
    $this->appendStep('welcome', true);
    $this->appendInstaller('Jivoo\Setup\LockInstaller');
    if ($this->m->units->isActive('Databases'))
      $this->appendInstaller('Jivoo\Databases\DatabaseInstaller');
    if ($this->m->units->isActive('Migrations'))
      $this->appendInstaller('Jivoo\Migrations\MigrationInstaller');
    if ($this->m->units->isActive('ActiveModels'))
      $this->appendInstaller('Jivoo\ActiveModels\ActiveModelInstaller');
    $this->appendStep('unlock');
  }
  
  /**
   * Display welcome page.
   * @param string $data POST data if any.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function welcome($data = null) {
    $this->viewData['title'] = tr('Welcome to %1', $this->app->name);
    if (isset($data))
      return $this->next();
    return $this->render();
  }

  /**
   * Exit maintenance mode.
   * @param string $data POST data if any.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function unlock($data = null) {
    if ($this->m->Setup->unlock(false))
      return $this->next();
    return $this->render();
  }
}
