<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;

/**
 * Initializes the request object.
 */
class Request extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->request = new \Jivoo\Routing\Request($config->get('cookiePrefix', ''));
  }
}