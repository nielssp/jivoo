<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Utilities;

/**
 * The Jivoo toolkit. Module for creating web application user interfaces.
 * @property-read IconMenu $menu A global menu object.
 */
class Jtk extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers');
  
  /**
   * @var string[]
   */
  private $tools = array();
  
  /**
   * @var JtkSnippet[]
   */
  private $toolInstances = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->m->Helpers->addHelper('Jivoo\Jtk\JtkHelper');
    $this->m->Helpers->addHelper('Jivoo\Jtk\IconHelper');
    $this->m->Helpers->addHelper('Jivoo\Jtk\ContentAdminHelper');
    
    $this->addTool('Jivoo\Jtk\Table\DataTable');
    $this->addTool('Jivoo\Jtk\Menu\Menu');
  }
  
  /**
   * Add a tool snippet.
   * @param string $class JtkObject class name.
   * @param string $snippetClass Optional name of snippet class. Default is
   * $class . 'Snippet'.
   */
  public function addTool($class, $snippetClass = null) {
    $name = Utilities::getClassName($class);
    if (!isset($snippetClass))
      $snippetClass = $class . 'Snippet';
    $this->tools[$name] = $snippetClass;
  }
  
  /**
   * Get a JTK tool/snippet.
   * @param string $name Tool name.
   * @return JtkSnippet Snippet object for tool.
   */
  public function getTool($name) {
//     if (isset($this->toolInstances[$name]))
//       return $this->toolInstances[$name];
    if (isset ($this->tools[$name])) {
      $class = $this->tools[$name];
      Utilities::assumeSubclassOf($class, 'Jivoo\Jtk\JtkSnippet');
      return new $class($this->app);
    }
    return null;
  }
}