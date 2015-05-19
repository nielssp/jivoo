<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Console;

use Jivoo\Snippets\Snippet;

/**
 * Snippet presented when application configuration is missing.
 */
class Index extends Snippet {
  protected $helpers = array('Jtk');
  
  public function get() {
    $this->viewData['appDir'] = $this->p('app', '');
    return $this->render();
  }
}