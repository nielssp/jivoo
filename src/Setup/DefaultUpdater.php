<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Setup;

/**
 * An updater that presents a welcome-page, runs the migration updater (if
 * enabled), and exits maintenance mode.
 */
class DefaultUpdater extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */  
  public function setup() {
    $this->appendStep('welcome');
    if ($this->m->hasImport('Migrations'))
      $this->appendInstaller('Jivoo\Migrations\MigrationUpdater');
    $this->appendStep('unlock');
  }

  /**
   * Display welcome page and attempt to enter maintenance mode.
   * @param string $data POST data if any.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function welcome($data = null) {
    if (!$this->m->Setup->isLocked()) {
      if (!$this->m->Setup->lock())
        throw new SetupException(tr('Update required. Could not enter maintenance mode.'));
      return $this->refresh();
    }
    $this->viewData['to'] = $this->app->version;
    $this->viewData['title'] = tr('Updating %1', $this->app->name);
    if (isset($this->app->state['setup']['version'])) {
      $this->viewData['from'] = $this->app->state['setup']['version'];
      $this->viewData['subtitle'] = tr(
        'From version %1 to version %2',
        '<strong>' . $this->viewData['from'] . '</strong>',
        '<strong>' . $this->viewData['to'] . '</strong>'
      );
    }
    else { 
      $this->viewData['subtitle'] = tr(
        'To version %2',
        '<strong>' . $this->viewData['to'] . '</strong>'
      );
    }
    if (isset($data))
      return $this->next();
    return $this->render();
  }

  /**
   * Exits maintenance mode.
   * @param string $data POST data if any.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function unlock($data = null) {
    if ($this->m->Setup->unlock(false))
      return $this->next();
    return $this->render('jivoo/setup/default-installer/unlock.html');
  }
}
