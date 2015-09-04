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
use Jivoo\Jtk\Jtk;

/**
 * Initializes the JTK system.
 */
class JtkUnit extends UnitBase {  
  /**
   * {@inheritdoc}
   */
  public function run(App $app, Document $config) {
    $this->m->lazy('Helpers')->addHelper('Jivoo\Jtk\JtkHelper');
    $this->m->lazy('Helpers')->addHelper('Jivoo\Jtk\IconHelper');
    $this->m->lazy('Helpers')->addHelper('Jivoo\Jtk\ContentAdminHelper');
  }
}