<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Snippets;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Lib;

/**
 * Snippets module.
 */
class Snippets extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Routing', 'View');

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Routing->dispatchers->add(new SnippetDispatcher($this->m->Routing, $this));
    if (is_dir($this->p('app', 'snippets'))) {
      Lib::import($this->p('app', 'snippets'), $this->app->n('Snippets'));
    }
  }
  
  /**
   * Get a snippet instance.
   * @param string $name Snippet class name.
   * @return ISnippet Snippet instance or null if not found.
   */
  public function getSnippet($name) {
    if (!Lib::classExists($name))
      $name = $this->app->n('Snippets\\' . $name);
    Lib::assumeSubclassOf($name, 'Jivoo\Snippets\Snippet');
    $instance = new $name($this->app);
    return $instance;
  }
}
