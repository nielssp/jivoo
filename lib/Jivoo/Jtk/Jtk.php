<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Lib;

/**
 * The Jivoo toolkit. Module for creating web application user interfaces.
 * @property-read IconMenu $menu A global menu object.
 */
class Jtk extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected $modules = array('Helpers');
  
  /*
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
    
    $this->addTool('Jivoo\Jtk\Form\FormField');
  }
  
  /**
   * Add a tool snippet.
   * @param string $class Class name.
   * @param string $name Optional tool name.
   */
  public function addTool($class, $name = null) {
    if (!isset($name))
      $name = Lib::getClassName($class);
    $this->tools[$name] = $class;
  }
  
  /**
   * 
   * @param string $name
   * @return JtkSnippet
   */
  public function getTool($name) {
    if (isset($this->toolInstances[$name]))
      return $this->toolInstances[$name];
    if (isset ($this->tools[$name])) {
      $class = $this->tools[$name];
      Lib::assumeSubclassOf($class, 'Jivoo\Jtk\JtkSnippet');
      $this->toolInstances[$name] = new $class($this->app);
      return $this->toolInstances[$name];
    }
    return null;
  }
}