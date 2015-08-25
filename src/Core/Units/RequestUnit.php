<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Core\Units;

use Jivoo\Core\UnitBase;
use Jivoo\Core\App;
use Jivoo\Core\Store\Document;
use Jivoo\Routing\Request;

/**
 * Initializes the request object.
 */
class RequestUnit extends UnitBase {
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $app->m->request = new Request($config->get('cookiePrefix', ''));
    $app->m->addProperty('request', $this->m->request);
    $app->m->addProperty('session', $this->m->request->session);
  }
}