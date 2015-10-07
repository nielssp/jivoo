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
use Jivoo\Databases\Loader;

/**
 * Initializes the database system.
 */
class DatabasesUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  protected $requires = array('AppLogic');

  /**
   * {@inheritdoc}
   */
  protected $after = array('Setup');
  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->db = new Loader($this->config->getSubset('Databases'));
    $app->m->db->setLogger($this->logger);
    $app->m->Databases = $app->m->db;
    

    if (isset($app->manifest['databases'])) {
      foreach ($app->manifest['databases'] as $name) {
        $schema = $this->m->db->readSchema($this->app->n('Schemas\\' . $name), $this->p('app/Schemas/' . $name));
        $app->m->db->connect($name, $schema);
      }
    }
    else {
      $schema = $this->m->db->readSchema($this->app->n('Schemas'), $this->p('app/Schemas'));
      $app->m->db->connect('default', $schema);
    }
  }
}