<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Core\LoadableModule;

/**
 * Snippets module.
 */
class Snippets extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing', 'Templates');

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Routing->dispatchers->add(new SnippetDispatcher($this->m->Routing, $this));
    if (is_dir($this->p('app', 'snippets'))) {
      Lib::import($this->p('app', 'snippets'), $this->app->n('Snippets'));
    }
  }
}
