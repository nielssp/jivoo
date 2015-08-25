<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Core\LoadableModule;
use Jivoo\Databases\DatabaseLoader;

/**
 * Initializes the database system.
 */
class DatabasesUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->db = new DatabaseLoader($app);

    if (isset($app->manifest['databases'])) {
      foreach ($app->manifest['databases'] as $name) {
        $app->m->db->attachDatabase($name, $this->p('app/Schemas/' . $name));
      }
    }
    else {
      $app->m->db->attachDatabase('default', $this->p('app/Schemas'));
    }
  }
}