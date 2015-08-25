<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\Store\StateMap;

/**
 * Initializes application state storage.
 */
class StateUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->state = new StateMap($this->p('state'));
    $app->m->addProperty('state', $app->m->state);
  }
}