<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Controllers\ActionDispatcher;
use Jivoo\Controllers\Controllers;
use Jivoo\Core\LoadableModule;
use Jivoo\Snippets\SnippetDispatcher;
use Jivoo\Snippets\Snippets;
use Jivoo\Routing\Routing;
use Jivoo\Assets\Assets;
use Jivoo\View\View;
use Jivoo\Core\Module;

/**
 * Initializes the application routes.
 */
class AppRoutesUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('Routing');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $this->m->Routing->loadRoutes();
  }
}