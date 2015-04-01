<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Jtk;

use Jivoo\Core\LoadableModule;
use Jivoo\Administration\Menu\IconMenu;
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
  
  /**
   * @var IconMenu Global menu.
   */
  private $menu;
  
  private $tools = array();

  /**
   * {@inheritdoc}
   */
  protected function init() {
    $this->menu = new IconMenu(tr('Administration'));
    $this->m->Helpers->addHelper('Jivoo\Jtk\JtkHelper');
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
   * {@inheritdoc}
   */
  public function __get($property) {
    switch ($property) {
      case 'menu':
        return $this->$property;
    }
    return parent::__get($property);
  }
}