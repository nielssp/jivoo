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
  protected $modules = array('Routing', 'View', 'Helpers');

  /**
   * @var ISnippet[] Snippet instances.
   */
  private $instances = array();
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Routing->dispatchers->add(new SnippetDispatcher($this->m->Routing, $this));
    if (is_dir($this->p('app', 'snippets'))) {
      Lib::import($this->p('app', 'snippets'), $this->app->n('Snippets'));
    }
    $this->m->Helpers->addHelper('Jivoo\Snippets\SnippetHelper');
  }
  
  /**
   * Get a snippet instance.
   * @param string $name Snippet class name.
   * @parm bool $singleton Whether to use an existing instance instead of
   * creating a new one.
   * @return ISnippet Snippet instance or null if not found.
   */
  public function getSnippet($name, $singleton = true) {
    if (!$singleton or !isset($this->instances[$name])) {
      $class = $name;
      if (!Lib::classExists($class))
        $class = $this->app->n('Snippets\\' . $name);
      Lib::assumeSubclassOf($class, 'Jivoo\Snippets\Snippet');
      $object = new $class($this->app);
      if (!$singleton)
        return $object;
      $this->instances[$name] = $object;
    }
    return $this->instances[$name];
  }
}
